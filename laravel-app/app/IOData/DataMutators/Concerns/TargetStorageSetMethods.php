<?php

namespace App\IOData\DataMutators\Concerns;

trait TargetStorageSetMethods
{
    protected ?\Illuminate\Filesystem\FilesystemAdapter $targetStorage = null;
    protected ?string $targetFileName = null;

    public function setTargetStorage(
        \Illuminate\Filesystem\FilesystemAdapter $targetStorage,
        ?string $targetFileName = null
    ): static {
        $this->targetStorage = $targetStorage;

        if ($targetFileName) {
            $this->setTargetFileName($targetFileName);
        }

        return $this;
    }

    public function getTargetStorage(): ?\Illuminate\Filesystem\FilesystemAdapter
    {
        return $this->targetStorage;
    }

    public function setTargetFileName(string $targetFileName): static
    {
        $this->targetFileName = $targetFileName;

        return $this;
    }

    public function getTargetFileName(?string $targetFileName = null): ?string
    {
        return $targetFileName ?: ($this->targetFileName ?? null) ?: null;
    }

    public function getTargetFileFullPath(?string $targetFileName = null): ?string
    {
        if (!$this->getTargetStorage()) {
            return null;
        }

        $fileName = $this->getTargetFileName($targetFileName);

        if (!$fileName) {
            return null;
        }

        return $this->getTargetStorage()?->path($fileName);
    }

    public function targetStorageIsValid(): bool
    {
        return $this->getTargetStorage() && $this->getTargetFileFullPath();
    }
}
