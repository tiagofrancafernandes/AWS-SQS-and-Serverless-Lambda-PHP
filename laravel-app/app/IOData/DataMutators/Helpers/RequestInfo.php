<?php

namespace App\IOData\DataMutators\Helpers;

use Illuminate\Support\Carbon;

/**
 * TODO: virar model?
 */
class RequestInfo
{
    public function __construct(
        // TODO
    )
    {
        //
    }

    public function reportFileUrl(): ?string
    {
        // TODO

        if (!$this->wasFinished()) {
            return null;
        }

        return null;
    }

    public function finishedFileUrl(): ?string
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

    public function getType(): string
    {
        // TODO
        return 'export';
    }

    public function getTenantId(): string
    {
        // TODO
        return 'tenant1';
    }

    public function getTable(): string
    {
        // TODO
        return 'public.users';
    }

    public function getResourceName(): string
    {
        // TODO
        return 'tenant_users';
    }

    public function getResource(): array
    {
        return $this->getResources()[$this->getResourceName()] ?? [];
    }

    public function getResourceProcessor(): ?string
    {
        return $this->getResource()[$this->getType()] ?? null;
    }

    public function getResources(): array
    {
        // TODO
        return [
            'tenant_users' => [
                'export' => \App\IOData\DataMutators\Exporters\UserExporter::class,
                'import' => \App\IOData\DataMutators\Importers\UserImporter::class,
            ],
        ];
    }

    public function getFilters(): array
    {
        // TODO
        return [];
    }

    public function getRequestDate(): Carbon
    {
        return \Illuminate\Support\Carbon::parse('2023-07-15 14:25:18');
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
                $this->getTable(),
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

    public function getMappedColumns(): array
    {
        return [
            'id' => 'ID',
            'name',
            'creator' => 'Conta criada por',
        ];
    }

    public function getModifiers(): array
    {
        return [
            // serialize(['where', ['id', '=', 2]]),
            serialize(['whereIn', ['id', [2, 4, 6]]]),
            // serialize(['orderBy', ['id', 'desc']]),
            // serialize(['whereNot', ['id', 3812]]),
        ];
    }
}
