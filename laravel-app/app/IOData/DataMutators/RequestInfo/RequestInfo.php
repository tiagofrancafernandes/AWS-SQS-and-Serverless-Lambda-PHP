<?php

namespace App\IOData\DataMutators\RequestInfo;

use Illuminate\Support\Carbon;
use App\IOData\DataMutators\Resources\ResourceManager;

/**
 * TODO: virar model?
 */
class RequestInfo
{
    protected array $mappedColumns;
    protected string $resourceName;
    protected array $modifiers;
    protected string $tenantId;

    public  function __construct(
        protected ?\App\IOData\DataMutators\Enums\RequestTypeEnum $type,
        array $mappedColumns,
        array $modifiers,
        string $resourceName,
        string $tenantId,
    ) {
        $this->setMappedColumns($mappedColumns);
        $this->setModifiers($modifiers);
        $this->setResourceName($resourceName);
        $this->setTenantId($tenantId);

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
            'tenantId' => $this->getTenantId(),
            'resourceName' => $this->getResourceName(),
            'mappedColumns' => $this->getMappedColumns(),
        ] as $param => $value) {
            if (!$value) {
                throw new \Exception('Invalid "' . $param . '" attribute.');
            }
        }
    }

    public function getType(): ?string
    {
        return $this->type?->name ?? null;
    }

    public function getTenantId(): string
    {
        return $this->tenantId ?? '';
    }

    public function setTenantId(string $tenantId): void
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

    public function getActionProcessor(): string
    {
        $this->validateInfo();

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
                $this->getRequestDate()->format('Y-m-d-His'),
                $this->getTenantId(),
                $this->getActionProcessor(),
                $this->getType(),
                ...array_filter($this->getFilters(), fn ($item) => $item && is_string($item) && trim($item)),
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

    public function getRequestDate(): Carbon
    {
        // TODO
        return \Illuminate\Support\Carbon::parse('2023-07-15 14:25:18');
    }

    public function getModifiers(): array
    {
        // TODO

        return [
            // serialize(['where', ['id', '=', 2]]),
            serialize(['whereIn', ['id', [2, 4, 6]]]),
            // serialize(['orderBy', ['id', 'desc']]),
            // serialize(['whereNot', ['id', 3812]]),
        ];
    }

    public function reportFileUrl(): ?string
    {
        // TODO

        if (!$this->wasFinished()) {
            return null;
        }

        return null;
    }

    public function finalFileUrl(): ?string
    {
        // TODO

        if (!$this->wasFinished()) {
            return null;
        }

        return null;
    }

    public function wasFinished(): bool
    {
        // TODO
        return false;
    }

    public function wasFinishedSuccessfully(): ?bool
    {
        // TODO

        if (!$this->wasFinished()) {
            return null;
        }

        return false;
    }
}
