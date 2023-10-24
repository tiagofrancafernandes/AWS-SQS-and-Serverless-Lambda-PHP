<?php

namespace App\IOData\DataMutators\Exporters;

use Closure;
use App\Models\Tenant;
use OpenSpout\Writer\XLSX\Writer;
use Illuminate\Support\Facades\DB;
use App\Helpers\Array\ArrayHelpers;
use App\Helpers\Tenancy\TenantRunner;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Border;
use Illuminate\Contracts\Support\Arrayable;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use App\IOData\DataMutators\RequestInfo\RequestInfo;
use Spatie\SimpleExcel\SimpleExcelWriter;
use App\Macroables\MacroableCollection;
use App\Helpers\DateForSearchFieldHelper;
use App\Helpers\TrueOrFalseHelper;

/**
 * @property ?SimpleExcelWriter $fileExcelWriter
 * @inheritDoc
 */
class RawQueryExporter extends Exporter
{
    protected ?Tenant $tenant = null;

    public function __construct(
        protected RequestInfo $requestInfo,
        protected mixed $options = null
    ) {
        $tenantId = $requestInfo->getTenantId();
        $this->tenant = $tenantId && $tenantId != 'global' ? Tenant::find($requestInfo->getTenantId()) : null;

        // if (!$this->tenant) {
        //     return;
        // }

        // TenantRunner::make()->initialize($this->tenant);
    }

    protected function handle()
    {
        DB::transaction(function () {
            if (!$this->tenant) {
                $this->process();
                return;
            }

            $output = TenantRunner::make([
                'requestInfo' => $this->requestInfo,
                'tenant' => $this->tenant,
                'exporter' => $this,
            ])
                ->run(
                    $this->tenant,
                    function (TenantRunner $runner) {
                        $params = $runner->getData(); // Exemplo de recuperação dos parâmetros

                        return $this->process();
                    }
                );

            dump($output);
        }, 2);
    }

    protected function process()
    {
        try {
            $defaultSort = [
                'id',
                'asc',
            ];

            if (!$this->fileExcelWriter) {
                throw new \Exception('Empty "fileExcelWriter"', 1);
            }

            // Útil para geração da planilha modelo
            $blankExcel = filter_var($this->requestInfo?->getSqsMessageAttribute('blankExcel'), FILTER_VALIDATE_BOOL);

            $filamentColumns = collect($this->requestInfo?->getFilamentColumns())
                ->filter(fn ($item) => isset($item['name']))
                ->mapWithKeys(function ($value) {
                    $key = is_string($value['dbColumn'] ?? null) ? trim($value['dbColumn']) : ($value['name'] ?? null);
                    return ["{$key}" => $value];
                });

            $filamentColumns->macro(...MacroableCollection::dotGet());

            $mappedColumns = static::stringKeys($this->requestInfo?->getMappedColumns() ?? []);
            $rawQueryData = $this->requestInfo?->getModifiers()['rawQuery'] ?? null;
            $rawSql = trim(strval($rawQueryData['sql'] ?? null));
            $rawSqlBindings = (array) ($rawQueryData['bindings'] ?? []);

            if (!$rawQueryData || !$rawSql) {
                throw new \Exception('Invalid "rawQueryData"');
            }

            $rowStyle = static::getRowStyle();
            $addedHeaders = false;

            foreach (DB::cursor($rawSql, $rawSqlBindings) as $record) {
                $modifiedRecord = [];
                collect($record)->each(function ($value, $key) use (
                    $mappedColumns,
                    &$modifiedRecord,
                    $filamentColumns,
                ) {
                    if (!filled($key) || !is_string($key)) {
                        return;
                    }

                    $filamentColumnInfo = $filamentColumns?->get($key) ?: static::getColunmDefaultSettings($key);

                    if (!$filamentColumnInfo) {
                        return;
                    }

                    $formatLabel = $filamentColumnInfo['formatLabel'] ?? null;
                    $formatValue = $filamentColumnInfo['formatValue'] ?? null;

                    $label = $filamentColumnInfo['label'] ?? null;

                    $newKey = is_callable($formatLabel) ? call_user_func($formatLabel, $label) : $label;

                    if (!$newKey) {
                        return;
                    }

                    $modifiedRecord[$newKey] = is_callable($formatValue)
                        ? call_user_func($formatValue, $value)
                        : static::castFormater($value);
                });

                if (!$addedHeaders) {
                    info('Excel headers', array_keys($modifiedRecord));

                    $this->fileExcelWriter->addHeader(array_keys($modifiedRecord));

                    $addedHeaders = true;
                }

                if (!$modifiedRecord) {
                    continue;
                }

                // Útil para geração da planilha modelo
                if ($blankExcel) {
                    continue;
                }

                $this->fileExcelWriter->addRow(static::validRow((array) $modifiedRecord), $rowStyle);
            }

            info('Used memory: ', [(memory_get_peak_usage(true) / 1024 / 1024)]);

            // $failStyle = static::getStyle('fail', $rowStyle);
        } catch (\Throwable $th) {
            \Log::error($th);

            print_r([
                'error' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'line' => __FILE__ . ':' . __LINE__,
            ]);

            $this->pushRunReturn([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => __FILE__ . ':' . __LINE__,
                'used_memory' => (memory_get_peak_usage(true) / 1024 / 1024),
            ]);

            if ($this->debugIsOn()) {
                throw $th;
            }
        }
    }

    protected function after()
    {
        TenantRunner::make()->end();
        parent::after();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        // // Exemplo
        // return DB::table('products');
        return DB::query();
    }

    public static function getAllowedColumns(): array
    {
        return [];
    }

    public static function getRelationshipData(?string $relationship = null): null|string|Closure
    {
        return null;
    }

    /**
     * getColunmDefaultSettings function
     *
     * @param string $column
     * @return array
     */
    public static function getColunmDefaultSettings($column): array
    {
        if (!is_string($column)) {
            return [];
        }

        return match (strtolower($column)) {
            'ativo_coluna_virtual',  'ativo coluna virtual',
            'ativo_coluna_ativo', 'ativo coluna ativo',
            'ativo', 'deleted_at', 'deleted at',
            'active', 'is_active', 'is active', => [ // Coluna deleted_at
                'name' => 'deleted_at',
                'label' => 'Ativo',
                'dbColumn' => 'deleted_at',
                'formatLabel' => ['Str', 'headline'],
                'formatValue' => function ($value = null) {
                    $value = trim("{$value}");
                    $deleted = boolval(
                        DateForSearchFieldHelper::get($value) ?: TrueOrFalseHelper::trueOrFalse($value)
                    );

                    return $deleted ? 0 : 1;
                },
            ],
            default => [],
        };
    }
}
