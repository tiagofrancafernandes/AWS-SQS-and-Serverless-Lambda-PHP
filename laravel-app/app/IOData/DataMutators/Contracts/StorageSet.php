<?php

namespace App\IOData\DataMutators\Contracts;

interface StorageSet
{
    public function setTempStorage(
        \Illuminate\Filesystem\FilesystemAdapter $tempLocalStorage,
        ?string $tempFileName = null
    ): static;

    public function getTempStorage(): ?\Illuminate\Filesystem\FilesystemAdapter;

    public function setTempFileName(string $tempFileName): static;

    public function getTempFileFullPath(?string $tempFileName = null): ?string;

    public function setTargetStorage(
        \Illuminate\Filesystem\FilesystemAdapter $targetStorage,
        ?string $targetFileName = null
    ): static;

    public function getTargetStorage(): ?\Illuminate\Filesystem\FilesystemAdapter;

    public function setTargetFileName(string $targetFileName): static;

    public function getTargetFileFullPath(?string $targetFileName = null): ?string;
}
