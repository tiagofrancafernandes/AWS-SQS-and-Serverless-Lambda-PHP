<?php

namespace App\IOData\DataMutators\RequestInfo;

use App\Models\ExportRequest;
use App\Models\ImportRequest;
use Illuminate\Support\Carbon;
use App\Enums\IORequestStatusEnum;
use Illuminate\Support\Collection;
use App\Models\Interfaces\RequestModel;
use Illuminate\Support\Facades\Storage;
use App\IOData\DataMutators\Enums\RequestTypeEnum;
use App\IOData\DataMutators\Resources\ResourceManager;

class RequestInfo
{
    protected array $mappedColumns;
    protected string $resourceName;
    protected array $modifiers;
    protected ?string $tenantId;

    public  function __construct(
        protected RequestModel $requestModel,
    ) {
        $this->initData();
    }

    /**
     * createRequestModel function
     *
     * @param string $requestType
     * @param array|Collection $requestModelData
     *
     * @return RequestModel|null
     */
    public static function createRequestModel(
        string $requestType,
        array|Collection $requestModelData,
    ): ?RequestModel {
        $requestType = trim(strtolower($requestType));

        if (!in_array($requestType, ['import', 'export'], true)) {
            return null;
        }

        return $requestType === 'import'
            ? ImportRequest::factory()->createOne(array_merge($requestModelData, [
                'import_file_url' => null, // TODO
                'import_file_disk_name' => null, // TODO
            ]))
            : ExportRequest::factory()->createOne($requestModelData);
    }

    /**
     * loadRequestModel function
     *
     * @param null|string|integer|RequestModel $requestModel
     * @param string|null $requestType
     *
     * @return RequestModel|null
     */
    public static function loadRequestModel(
        null|string|int|RequestModel $requestModel,
        ?string $requestType = null,
    ): ?RequestModel {
        $requestType = trim(strtolower((string) $requestType));

        if (is_a($requestModel, RequestModel::class)) {
            return $requestModel;
        }

        if (!in_array($requestType, ['import', 'export'], true)) {
            return null;
        }

        return $requestType === 'import' ? ImportRequest::find($requestModel) : ExportRequest::find($requestModel);
    }

    protected function initData()
    {
        $this->setMappedColumns($this->requestModel->getMappedColumns()?->toArray());
        $this->setModifiers($this->requestModel->getModifiers()?->toArray());
        $this->setResourceName($this->requestModel->getResourceName());
        $this->setTenantId($this->requestModel->getTenantId());

        $this->validateInfo();
    }

    protected function setModifiers(array $modifiers): void
    {
        $this->modifiers = array_filter($modifiers);
    }

    protected function validateInfo()
    {
        foreach ([
            'type' => $this->getType(),
            'resourceName' => $this->getResourceName(),
            'mappedColumns' => $this->getMappedColumns(),
            'actionProcessor' => $this->getActionProcessor(),
        ] as $param => $value) {
            if (!$value) {
                throw new \Exception('Invalid "' . $param . '" attribute.');
            }
        }
    }

    public function getTypeEnum(): RequestTypeEnum
    {
        return $this->requestModel->requestType();
    }

    public function getType(): ?string
    {
        return $this->getTypeEnum()?->value ?? null;
    }

    public function getTenantId(): string
    {
        return $this->tenantId ?? '';
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getResourceName(): string
    {
        return $this->resourceName ?? '';
    }

    protected function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getActionProcessor(): ?string
    {
        return ResourceManager::getActionProcessor(
            $this->getResourceName(),
            $this->getType(),
        );
    }

    public function getFilters(): array
    {
        return $this->getMappedColumns();
    }

    public function getRequestSlug(
        string $separator = '-',
        string $language = 'en',
        array $dictionary = ['@' => 'at']
    ): string {
        $items = array_filter(
            [
                $this->getRequestDate()?->format('Y-m-d-His'),
                $this->getTenantId(),
                $this->getResourceName(),
                $this->getType(),
                md5(
                    implode(
                        '-',
                        array_filter(
                            $this->getFilters(),
                            fn ($item) => $item && is_string($item) && trim($item)
                        )
                    )
                ),
            ]
        );

        return str(implode('_', $items))
            ->slug(
                $separator,
                $language,
                $dictionary,
            );
    }

    protected function setMappedColumns(array $mappedColumns): void
    {
        $this->mappedColumns = array_filter($mappedColumns, fn ($item) => is_string($item) && !is_numeric($item));
    }

    public function getMappedColumns(): array
    {
        return $this->mappedColumns ?? [];
    }

    public function getRequestDate(): ?Carbon
    {
        return $this->requestModel?->getRequestDate() ?? null;
    }

    public function getModifiers(): array
    {
        return $this->requestModel?->getModifiers()?->toArray() ?? [];
    }

    public function setReportFile(
        string $reportFilePath,
        string $reportFileDisk,
    ): mixed {
        return $this->requestModel?->setReportFile(
            $reportFilePath,
            $reportFileDisk,
        );
    }

    public function getReportFileUrl(): ?string
    {
        return $this->requestModel?->getReportUrl() ?? null;
    }

    public function setFinalFileUrl(
        string $finalFilePath,
        string $finalFileDisk,
    ): mixed {
        if (!$finalFilePath || !$finalFileDisk) {
            return null;
        }

        if (!in_array($finalFileDisk, array_keys(config('filesystems.disks')))) {
            throw new \Exception('Invalid "finalFileDisk"', 1);
        }

        return $this->updateRequestModel([
            'final_file_path' => $finalFilePath,
            'final_file_disk' => $finalFileDisk,
        ]);
    }

    public function getFinalFileUrl(): ?string
    {
        return $this->requestModel?->getFinalFileUrl() ?? null;
    }

    public function getRequestModel(): RequestModel
    {
        return $this->requestModel;
    }

    /**
     * @suppress PHP0418
     * @see https://docs.devsense.com/pt/vscode/problems#suppress-phpdoc-tag
     */
    public function updateRequestModel(array $attributes): mixed
    {
        return $this->requestModel?->update($attributes);
    }

    public function setAsFinished(int $finalStatus): bool
    {
        return $this->requestModel?->setAsFinished($finalStatus) ?? false;
    }

    public function setStatus(int $status): bool
    {
        $status = IORequestStatusEnum::get($status) ? $status : null;

        if (!$status) {
            return false;
        }

        return $this->updateRequestModel([
            'status' => $status,
        ]);
    }

    public function getStatus(): ?int
    {
        return $this->requestModelGet('final_status') ?: $this->requestModelGet('status');
    }

    public function wasFinished(): bool
    {
        return $this->requestModel?->wasFinished() ?? false;
    }

    public function wasFinishedSuccessfully(): ?bool
    {
        if (!$this->wasFinished()) {
            return null;
        }

        return $this->requestModel?->{'was_finished_successfully'} ?? false;
    }

    public function requestModelGet(?string $key, mixed $defaultValue = null): mixed
    {
        if (!$key) {
            return null;
        }

        return $this->requestModel?->{$key} ?? $defaultValue ?? null;
    }
}
