<?php

namespace App\IOData\DataMutators\Contracts;

interface Importer extends StorageSet
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

    public function todo(): string; // REMOVER

    /*
        TODO:
        - metodo para obter o caminho/URL do arquivo de importação
        - metodo para obter o caminho/URL do arquivo de importação, report e arquivo gerado para exportação
        - método que apaga o arquivo de importação (usado para report na importação e arquivo de exportação)
        - método que define a visibilidade do arquivo a ser salvo (usado para report na importação e arquivo de exportação)
    */
}
