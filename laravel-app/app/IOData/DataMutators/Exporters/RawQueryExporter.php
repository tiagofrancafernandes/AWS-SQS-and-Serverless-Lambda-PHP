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

            $mappedColumns = static::stringKeys($this->requestInfo?->getMappedColumns() ?? []);
            $rawQueryData = $this->requestInfo?->getModifiers()['rawQuery'] ?? null;
            $rawSql = trim(strval($rawQueryData['sql'] ?? null));
            $rawSqlBindings = (array) ($rawQueryData['bindings'] ?? []);

            if (!$rawQueryData || !$rawSql) {
                throw new \Exception('Invalid "rawQueryData"');
            }

            $rowStyle = static::getRowStyle();
            $addedHeaders = false;

            foreach (DB::cursor( $rawSql, $rawSqlBindings ) as $record) {
                $modifiedRecord = [];
                collect($record)->each(function ($value, $key) use ($mappedColumns, &$modifiedRecord) {
                    $newKey = $key && is_string($key) ? $mappedColumns[$key] ?? $key : $key;

                    $modifiedRecord[$newKey] = static::castFormater($value);
                });

                if (!$addedHeaders) {
                    $this->fileExcelWriter->addHeader(array_keys($modifiedRecord));

                    $addedHeaders = true;
                }

                if (!$modifiedRecord) {
                    continue;
                }

                $this->fileExcelWriter->addRow(static::validRow((array) $modifiedRecord), $rowStyle);
            }

            echo 'Used memory: ' . (memory_get_peak_usage(true) / 1024 / 1024) . PHP_EOL;

            // $failStyle = static::getStyle('fail', $rowStyle);
        } catch (\Throwable $th) {
            \Log::error($th);

            print_r([
                'error_line' => $th->getLine(),
                'error' => $th->getMessage(),
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
        return DB::table('products');
    }

    public static function getAllowedColumns(): array
    {
        return [];
    }

    public static function getRelationshipData(?string $relationship = null): null|string|Closure
    {
        return null;
    }
}
