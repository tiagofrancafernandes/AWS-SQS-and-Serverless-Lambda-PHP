<?php

namespace App\IOData\DataMutators\Importers;

use App\IOData\DataMutators\Concerns\TargetStorageSetMethods;
use App\IOData\DataMutators\Concerns\TempStorageSetMethods;
use App\IOData\DataMutators\Contracts\Importer as ContractsImporter;

abstract class Importer implements ContractsImporter
{
    use TempStorageSetMethods;
    use TargetStorageSetMethods;

    protected null|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query = null;

    public function init(
        \Illuminate\Filesystem\FilesystemAdapter $tempLocalStorage,
        \Illuminate\Filesystem\FilesystemAdapter $targetStorage,
        ?string $tempFileName = null,
        ?string $targetFileName = null,
    ): static {
        $this->setTempStorage($tempLocalStorage, $tempFileName);
        $this->setTargetStorage($targetStorage, $targetFileName);
        return $this;
    }

    public function setQuery(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        return $this->query ?? app(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder::class);
    }
}
