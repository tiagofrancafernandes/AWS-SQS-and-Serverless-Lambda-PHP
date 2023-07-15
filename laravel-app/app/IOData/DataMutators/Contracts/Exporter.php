<?php

namespace App\IOData\DataMutators\Contracts;

interface Exporter extends StorageSet
{
    public function init(
        \Illuminate\Filesystem\FilesystemAdapter $tempLocalStorage,
        \Illuminate\Filesystem\FilesystemAdapter $targetStorage,
        ?string $tempFileName = null,
        ?string $targetFileName = null,
    ): static;

    public function setQuery(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query): static;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder;

    public static function setAllowedColumns(array $allowedColumns, bool $merge = false): void;

    public static function getAllowedColumns(): array;

    public function setStatus(int $status): void;

    public function isFinished(): bool;

    public function setFinished(bool $success = true): void;

    public function getCurrentStatus(): int;
}
