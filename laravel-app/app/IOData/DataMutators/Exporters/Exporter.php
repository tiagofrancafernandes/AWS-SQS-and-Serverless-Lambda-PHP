<?php

namespace App\IOData\DataMutators\Exporters;

use Closure;
use Illuminate\Support\Fluent;
use App\Helpers\File\FileHelpers;
use OpenSpout\Writer\XLSX\Writer;
use \App\Enums\IORequestStatusEnum;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Border;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Filesystem\FilesystemAdapter;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use App\IOData\DataMutators\RequestInfo\RequestInfo;
use App\IOData\DataMutators\Concerns\TempStorageSetMethods;
use App\IOData\DataMutators\Concerns\TargetStorageSetMethods;
use App\IOData\DataMutators\Contracts\Exporter as ContractsExporter;

abstract class Exporter implements ContractsExporter
{
    use TempStorageSetMethods;
    use TargetStorageSetMethods;

    protected null|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query = null;
    protected ?FilesystemAdapter $tempLocalStorage = null;
    protected ?FilesystemAdapter $targetStorage = null;
    protected ?FilesystemAdapter $reportLocalStorage = null;
    protected ?FilesystemAdapter $reportFinalStorage = null;
    protected ?SimpleExcelWriter $fileExcelWriter = null;
    protected RequestInfo $requestInfo;
    protected static ?array $allowedColumns = [];
    protected ?string $reportFileName = null;
    protected bool $loaded = false;
    protected ?bool $debug = null;
    protected ?bool $handledSuccessfully = null;
    protected ?int $status = null;
    protected array $allRunReturn = [];
    protected mixed $beforeResult = null;
    protected mixed $handleResult = null;
    protected mixed $afterResult = null;
    protected array $steps = [];

    abstract public function __construct(RequestInfo $requestInfo, mixed $options = null);

    public function init(
        FilesystemAdapter $tempLocalStorage,
        FilesystemAdapter $targetStorage,
        ?string $tempFileName = null,
        ?string $targetFileName = null,
    ): static {
        $this->setTempStorage($tempLocalStorage, $tempFileName);
        $this->setTargetStorage($targetStorage, $targetFileName);

        $this->setStatus(IORequestStatusEnum::INITIALIZED, true);

        $this->addStep(__METHOD__, date('c'));

        return $this;
    }

    protected function loadInit()
    {
        if ($this->loaded) {
            return;
        }

        $this->init(
            Storage::disk(config('import-export.export.temp_disk')),
            Storage::disk(config('import-export.export.target_disk')),
        );

        $this->reportLocalStorage = Storage::disk(config('import-export.export.report_local_disk'));
        $this->reportFinalStorage = Storage::disk(config('import-export.export.report_final_disk'));

        $this->loaded = true;
    }

    protected function getSteps()
    {
        return $this->steps;
    }

    protected function handledSuccessfully(): bool
    {
        return boolval($this->handledSuccessfully);
    }

    protected function addStep($step, ...$aditionalInfo): void
    {
        // TODO: colocar opção de salvar em log
        // Storage::disk('tmp')->append('algo.txt', 'algo4')

        if (!$step) {
            return;
        }

        logAndDump([$step, ...$aditionalInfo]); // TODO Ver se ficará mesmo aqui

        $step = $aditionalInfo ? [$step, $aditionalInfo] : $step;

        $this->steps[] = $step;
    }

    public function setQuery(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query): static
    {
        $this->query = $query;

        return $this;
    }

    public static function setAllowedColumns(array $allowedColumns, bool $merge = false): void
    {
        if (!$merge) {
            static::$allowedColumns = $allowedColumns;

            return;
        }

        static::$allowedColumns = array_merge(
            static::getAllowedColumns(),
            $allowedColumns,
        );
    }

    public static function getAllowedColumns(): array
    {
        return static::$allowedColumns ?? [];
    }

    public function setStatus(int $status, bool $addToStep = false): void
    {
        $statusName = IORequestStatusEnum::get($status);

        if ($addToStep) {
            $this->addStep(spf('setStatus: %s [%s]', $statusName, $status));
        }

        $isAFinalStatus = in_array($status, IORequestStatusEnum::finishedStatusList(), true);

        $setStatusResult = $isAFinalStatus
            ? $this->requestInfo->setAsFinished($status)
            : $this->requestInfo->setStatus($status);

        $calledBy = FileHelpers::formatDebugBacktrace(debug_backtrace(), true);
        logAndDumpSpf(
            'setStatus: %s | calledBy: %s | isAFinalStatus: %s | setStatusResult: %s',
            spf($statusName . ' [%s]', $status),
            $calledBy,
            var_export($isAFinalStatus, true),
            var_export($setStatusResult, true),
        );
    }

    public function isFinishedAsSuccess(): bool
    {
        return $this->getCurrentStatus() === IORequestStatusEnum::FINISHED;
    }

    public function debug(bool $debug = true): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function debugIsOn(): bool
    {
        return $this->debug ?? config('import-export.debug', false);
    }

    public function isFinished(): bool
    {
        return in_array($this->getCurrentStatus(), IORequestStatusEnum::finishedStatusList(), true);
    }

    public function setFinished(bool $success = true): void
    {
        logAndDumpSpf('%s | calledBy: %s', __FUNCTION__, formatDebugBacktrace(debug_backtrace(), true));

        if ($this->isFinished()) {
            return;
        }

        $finalStatus = $success ? IORequestStatusEnum::FINISHED : IORequestStatusEnum::FINISHED_WITH_FAIL;

        $this->requestInfo?->setAsFinished($finalStatus);

        logAndDumpSpf('finalStatus: %s [%s]', IORequestStatusEnum::get($finalStatus), $finalStatus);
    }

    public function getStatus(): int
    {
        return $this->getCurrentStatus();
    }

    public function getStatusName(): ?string
    {
        return $this->getCurrentStatusName();
    }

    public function getCurrentStatus(): int
    {
        return $this->requestInfo?->getStatus() ?? IORequestStatusEnum::UNDEFINED;
    }

    public function getCurrentStatusName(): ?string
    {
        return IORequestStatusEnum::get($this->getCurrentStatus());
    }

    protected function run(): ?bool
    {
        $this->loadInit();

        $this->addStep(
            __FUNCTION__,
            date('c'),
            __METHOD__,
            'isFinished:' . var_export($this->isFinished(), true)
        );

        if ($this->isFinished()) {
            $message = 'This process has already been finished';

            if ($this->debugIsOn()) {
                throw new \Exception($message, __LINE__);
            }

            $this->pushRunReturn([
                'success' => false,
                'message' => $message,
                'status' => $this->getCurrentStatus(),
            ]);

            $this->addStep(__FUNCTION__, date('c'), $this->getLastRunReturn());

            return false;
        }

        /**
         * before start
         */
        $this->addStep('before', date('c'));
        $this->setStatus(IORequestStatusEnum::BEFORE_BEFORE_STEP_RUN, true);
        $this->beforeResult = $this->runStep(
            fn () => $this->before(),
            beforeRunStatus: IORequestStatusEnum::BEFORE_STEP_RUNNING,
            errorStatus: IORequestStatusEnum::FINISHED_WITH_FAIL,
        );

        if ($this->isFinished()) {
            $message = 'This process has already been finished';

            if ($this->debugIsOn()) {
                throw new \Exception($message, __LINE__);
            }

            $this->pushRunReturn([
                'success' => false,
                'message' => $message,
                'status' => $this->getCurrentStatus(),
                'line' => __FILE__ . ':' . __LINE__,
            ]);

            $this->addStep('break flow after "before"', date('c'), $this->getLastRunReturn());

            return false;
        }
        /**
         * before end
         */

        /**
         * handle start
         */
        $this->addStep('handle', date('c'));

        if (!$this->validationBeforeHandleRun()) {
            $this->addStep('Fail on validationBeforeHandleRun', date('c'), $this->getLastRunReturn());
            return false;
        }

        $this->handleResult = $this->runStep(
            fn () => $this->handle(),
            beforeRunStatus: IORequestStatusEnum::HANDLE_BEFORE,
            successStatus: IORequestStatusEnum::HANDLE_SUCCESS,
            errorStatus: IORequestStatusEnum::HANDLE_FAIL,
        );

        if ($this->isFinished()) {
            $message = 'This process has already been finished';

            if ($this->debugIsOn()) {
                throw new \Exception($message, __LINE__);
            }

            $this->pushRunReturn([
                'success' => false,
                'message' => $message,
                'status' => $this->getCurrentStatus(),
                'line' => __FILE__ . ':' . __LINE__,
            ]);

            $this->addStep('break flow after "handle"', date('c'), $this->getLastRunReturn());

            return false;
        }
        /**
         * handle end
         */

        logAndDumpSpf('getStatus: %s', var_export($this->getStatus(), true));
        $this->handledSuccessfully = $this->getStatus() === IORequestStatusEnum::HANDLE_SUCCESS;

        /**
         * after start
         */
        $this->addStep('after', date('c'));
        $this->setStatus(IORequestStatusEnum::BEFORE_AFTER_STEP_RUN, true);
        $this->afterResult = $this->runStep(
            fn () => $this->after(),
            beforeRunStatus: IORequestStatusEnum::AFTER_STEP_RUNNING,
            errorStatus: IORequestStatusEnum::HANDLE_FAIL,
            successStatus: IORequestStatusEnum::AFTER_STEP_DONE,
        );

        if ($this->isFinished()) {
            $message = 'This process has already been finished';

            if ($this->debugIsOn()) {
                throw new \Exception($message, __LINE__);
            }

            $this->pushRunReturn([
                'success' => false,
                'message' => $message,
                'status' => $this->getCurrentStatus(),
                'line' => __FILE__ . ':' . __LINE__,
            ]);

            $this->addStep('break flow after "after"', date('c'), $this->getLastRunReturn());

            return false;
        }
        /**
         * after end
         */

         if (!$this->isFinished()) {
            logAndDumpSpf(
                'Setting as finished with status: %s [%s]',
                $this->getCurrentStatusName(),
                $this->getCurrentStatus()
            );

            // Se deve fechar como sucesso ou falha
            $this->setFinished(
                $this->getCurrentStatus() &&
                    !in_array($this->getCurrentStatus(), IORequestStatusEnum::notSuccessStatusList(), true)
            );
        }

        $this->pushRunReturn([
            'success' => $this->isFinishedAsSuccess(),
            'message' => 'End of process',
            'status' => $this->getCurrentStatus(),
        ]);

        return true;
    }

    final protected function runStep(
        Closure $callable,
        ?int $beforeRunStatus = null,
        ?int $successStatus = null,
        ?int $errorStatus = null,
    ): mixed {
        try {
            if ($beforeRunStatus) {
                $this->setStatus($beforeRunStatus, true);
            }

            $result = $callable();

            if (is_bool($result) && ($result === false) && $errorStatus) {
                $this->setStatus($errorStatus, true);

                return $result;
            }

            $successStatus = $successStatus ?: IORequestStatusEnum::GENERIC_SUCCESS;

            if ($successStatus) {
                $this->setStatus($successStatus, true);
            }

            return $result;
        } catch (\Throwable $th) {
            if ($errorStatus) {
                $this->setStatus($errorStatus, true);
            }

            if ($this->debugIsOn()) {
                throw $th;
            }

            return null;
        }
    }

    final public function runProcess(): static
    {
        $this->run();

        return $this;
    }

    public function getAllRunReturn(): array
    {
        return $this->allRunReturn;
    }

    public function getLastRunReturn(): mixed
    {
        return last($this->allRunReturn) ?: null;
    }

    public function pushRunReturn(mixed $runReturn)
    {
        $this->allRunReturn[] = $runReturn;
    }

    protected function validationBeforeHandleRun()
    {
        $this->addStep(__FUNCTION__);

        if ($this->targetStorageIsValid() && $this->tempStorageIsValid()) {
            return true;
        }

        $message = 'Some requirements was not assert';

        $this->pushRunReturn([
            'success' => false,
            'message' => $message,
            'targetStorageIsValid' => var_export($this->targetStorageIsValid(), true),
            'tempStorageIsValid' => var_export($this->tempStorageIsValid(), true),
        ]);

        if ($this->debugIsOn()) {
            throw new \Exception(
                sprintf(
                    '%s | targetStorageIsValid: %s | tempStorageIsValid: %s',
                    ...[
                        $message,
                        var_export($this->targetStorageIsValid(), true),
                        var_export($this->tempStorageIsValid(), true),
                    ]
                ),
                __LINE__
            );
        }

        $this->addStep(__FUNCTION__, date('c'), $this->getLastRunReturn());

        return false;
    }

    protected function process()
    {
        if (!$this->fileExcelWriter) {
            throw new \Exception('Empty "fileExcelWriter"', 1);
        }

        $rowStyle = (new Style())
            ->setCellAlignment(CellAlignment::LEFT)
            ->setShouldWrapText(false);

        $mappedColumns = static::stringKeys($this->requestInfo?->getMappedColumns() ?? []);

        $columns = static::getColumns(array_keys($mappedColumns));

        $query = static::modifyQuery(
            $this->getQuery(),
            $columns,
            $this->requestInfo?->getModifiers() ?? []
        )
            ->select(static::getTableColumns($columns));

        $this->fileExcelWriter->addHeader(array_values($mappedColumns));

        $query->chunk(100, function ($records) use (
            $columns,
            $rowStyle,
            $mappedColumns,
        ) {
            foreach ($records as $record) {
                $recordFormatedData = [];

                foreach (array_keys($mappedColumns) as $attribute) {
                    $formater = ($columns?->get($attribute) ?? [])['format'] ?? null;

                    $value = is_a($formater, 'Closure')
                        ? static::castFormater($formater($record))
                        : (is_string($attribute) ? static::castFormater($record?->{$attribute}) : null);

                    $recordFormatedData[$attribute] = $value;
                }

                $this->fileExcelWriter->addRow(static::validRow($recordFormatedData), $rowStyle);
            }
        });

        // $failStyle = static::getStyle('fail', $rowStyle);
    }

    public static function castFormater(mixed $value, mixed $defaultValue = null)
    {
        try {
            // TODO: aqui usar cast por tipos

            if (in_array(gettype($value), [
                'string', 'int', 'integer', 'float', 'double',
                'bool', 'null', 'boolean', 'NULL',
            ])) {
                return $value;
            }

            if (is_object($value)) {
                $className = get_class($value);

                return match ($className) {
                    'Illuminate\Support\Carbon' => (string) $value,
                    'Closure' => (string) $value(),
                    default => (string) $value,
                };
            }

            return trim(
                var_export($value, true),
                "'"
            );
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());

            return $defaultValue ?? $value;
        }
    }

    public static function validRow(array $data): array
    {
        foreach ($data as $key => $value) {
            if (
                in_array(gettype($value), [
                    'string', 'int', 'integer', 'float', 'double',
                    'bool', 'null', 'boolean', 'NULL',
                ])
                || is_a($value, 'DateTimeInterface')
                || is_a($value, 'DateInterval')
            ) {
                continue;
            }

            $data[$key] = var_export(
                is_object($value) ? collect($value)->toArray() : $value,
                true,
            );
        }

        return $data ?? [];
    }

    public static function stringKeys(array $data, array $remap = []): array
    {
        foreach ($data as $key => $value) {
            unset($data[$key]);

            if (!is_string($key) && !is_string($value)) {
                continue;
            };

            if (!is_string($key)) {
                $key = $value;
            };

            if (!is_string($key)) {
                continue;
            };

            $value = ($remap[$key] ?? null) ?: $value;

            $mappedKeys[$key] = $value;
        };

        return (array) ($mappedKeys ?? []);
    }

    public static function getTableColumns(\Illuminate\Support\Collection $columns): ?array
    {
        return $columns
            ->whereNotNull('table_column')
            ->map(fn ($item) => $item['table_column'] ?? null)
            ->values()
            ->unique()
            ->toArray();
    }

    public static function getRelationships(array|\Illuminate\Support\Collection $columns = []): array
    {
        if (!$columns) {
            return [];
        }

        return collect($columns)
            ->whereNotNull('table_column')
            ->whereNotNull('relationships')
            ->filter(fn ($item) => $item['table_column'] ?? null)
            ->map(fn ($item) => $item['relationships'] ?? null)
            // ->values()
            ->unique()
            ->flatMap(fn ($item) => $item)
            ->toArray();
    }

    public function getColumns(...$only): \Illuminate\Support\Collection
    {
        $collect = collect(static::getAllowedColumns())
            ->whereNotNull('table_column');

        $only = is_array($only[0] ?? null) ? $only[0] : $only;
        $only = array_filter([...$only], fn ($item) => $item && is_string($item));

        if (!$only) {
            return $collect;
        }

        return $collect->only($only);
    }

    public static function applyRelationships(
        \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query,
        array|\Illuminate\Support\Collection $columns
    ): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder {
        $relationships = [];

        foreach (static::getRelationships($columns) as $relationshipName) {
            $relationshipName = is_string($relationshipName) ? trim($relationshipName) : null;

            if (!$relationshipName) {
                continue;
            }

            $relationshipData = static::getRelationshipData($relationshipName);

            if (!$relationshipData) {
                continue;
            }

            $relationships[$relationshipName] = $relationshipData;
        }

        if ($relationships) {
            $query = $query->with($relationships);
        }

        return $query;
    }

    public static function modifyQuery(
        \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query,
        array|\Illuminate\Support\Collection $columns,
        array $modifiers = []
    ): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder {
        $query = static::applyRelationships($query, $columns);

        if (!$modifiers) {
            return $query;
        }

        foreach ($modifiers as $modifier) {
            if (!(is_string($modifier) || is_array($modifier))) {
                throw new \Exception('The modifiers must be an array or serialized array');
            }

            $unserialized = is_string($modifier) ? unserialize($modifier) : $modifier;

            if (!$unserialized || !is_array($unserialized) || count($unserialized) != 2) {
                continue;
            }

            $method = $unserialized[0] ?? null;
            $method = is_string($method) && in_array($method, [
                'where',
                'whereNull',
                'whereNotNull',
                'firstWhere',
                'orWhere',
                'whereNot',
                'orWhereNot',
                'orderBy',
                'whereIn',
            ], true) ? $method : null;

            if (!$method) {
                continue;
            }

            $params = $unserialized[1] ?? null;

            $params = is_array($params) && array_values($params) ? array_values($params) : null;

            if (!$params) {
                return $query;
            }

            $query = $query->{$method}(...$params);
        }

        return $query;
    }

    public static function fluent(mixed $data = null): Fluent
    {
        return new Fluent(is_iterable($data) ? $data : (array) $data);
    }

    public static function getStyle(string $styleName, ?Style $style = null): ?Style
    {
        /* Create a border around a cell */
        $border = new Border(
            new BorderPart(Border::BOTTOM, Color::LIGHT_BLUE, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::LIGHT_BLUE, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::LIGHT_BLUE, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::TOP, Color::LIGHT_BLUE, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        $styles = [
            'fail' => fn (Style $style) => $style
                ->setFontSize(13)
                ->setFontBold()
                ->setFontColor(Color::RED)
                ->setBackgroundColor(Color::YELLOW),

            'big_yellow' => fn (Style $style) => $style
                ->setFontSize(13)
                ->setFontBold()
                ->setFontColor(Color::BLUE)
                ->setBackgroundColor(Color::YELLOW)
                ->setCellAlignment(CellAlignment::LEFT)
                ->setShouldWrapText()
                ->setBorder($border),

            'header_yellow' => fn (Style $style) => $style
                ->setFontSize(13)
                ->setFontBold()
                ->setFontColor(Color::BLUE)
                ->setBackgroundColor(Color::YELLOW)
                ->setCellAlignment(CellAlignment::CENTER)
                ->setShouldWrapText(false)
                ->setBorder($border),
        ];

        $styleInfo = $styles[$styleName] ?? null;

        if (!$styleInfo) {
            return null;
        }

        return $styleInfo($style ?? new Style());
    }

    protected function before()
    {
        // Pode ser implementado no filho para configurações etc
        $this->addStep(__METHOD__, date('c'), $this->getLastRunReturn());

        $this->reportFileName = $this->requestInfo->getRequestSlug() . '-report.xlsx';

        $this->setTempFileName($this->requestInfo->getRequestSlug() . '-temp.xlsx');
        $this->setTargetFileName($this->requestInfo->getRequestSlug() . '.xlsx');

        $this->fileExcelWriter = SimpleExcelWriter::create(
            $this->getTempFileFullPath(),
            configureWriter: function (Writer $writer) {
                $options = $writer->getOptions();
                $options->DEFAULT_COLUMN_WIDTH = 20; // set default width
                $options->DEFAULT_ROW_HEIGHT = 15; // set default height

                # set columns 2 and 10 to width 40
                $options->setColumnWidth(40, 2, 10);

                # set columns 1, 3 and 8 to width 30
                $options->setColumnWidth(30, 1, 3, 8);

                # set columns 9 through 12 to width 10
                // $options->setColumnWidthForRange(10, 9, 12);
            }
        );

        $headerStyle = static::getStyle('header_yellow');

        $this->fileExcelWriter->setHeaderStyle($headerStyle);
    }

    protected function after()
    {
        // Pode ser implementado no filho para ações como eviar email, notificações etc
        $this->addStep(__METHOD__, date('c'), $this->getLastRunReturn());

        $success = $this->handledSuccessfully();

        if (!$success) {
            // Aqui colocar alguma notificação, log etc

            logAndDump('handledSuccessfully FALSE', currentFileAndLine(true));

            return;
        }

        $this->fileExcelWriter?->close();
        $this->fileExcelWriter?->__destruct();

        $tempFileIsFilled = isFilledFile($this->fileExcelWriter?->getPath());

        if (!$tempFileIsFilled) {
            // Aqui colocar alguma notificação, log etc

            logAndDump('tempfileis no filled', currentFileAndLine(true));

            return;
        }

        $dirPrefix = $this->requestInfo?->getTenantId() ?: 'exports-' . date('Y-m-d');

        $finalPathToSave = $dirPrefix . '/' . $this->getTargetFileName();

        if ($this->getTargetStorage()?->exists($finalPathToSave)) {
            unlink(
                $this->getTargetStorage()?->path($finalPathToSave)
            );
        }

        $this->getTargetStorage()?->put(
            $finalPathToSave,
            file_get_contents($this->fileExcelWriter?->getPath())
            // file_get_contents($this->getTempFileFullPath())
        );

        if ($this->getTargetStorage()?->exists($finalPathToSave)) {
            $this->requestInfo?->setFinalFileUrl(
                $finalPathToSave,
                config('import-export.export.target_disk') ?? ''
            );
        }

        logAndDump('Removing temp file...');

        unlink($this->fileExcelWriter?->getPath());

        logAndDump(
            'Removed temp file?: ' . var_export(!is_file($this->fileExcelWriter?->getPath()), true),
            currentFileAndLine(true),
        );
    }

    abstract public function getQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder;

    abstract protected function handle();

    abstract public static function getRelationshipData(?string $relationship = null): null|string|Closure;
    /*
    public static function getRelationshipData(?string $relationship = null): null|string|Closure
    {
        if (!$relationship) {
            return null;
        }

        $relationships = [
            'creator' => fn ($query) => $query->select('id', 'name'),
        ];

        return $relationships[$relationship] ?? null;
    }
    */
}
