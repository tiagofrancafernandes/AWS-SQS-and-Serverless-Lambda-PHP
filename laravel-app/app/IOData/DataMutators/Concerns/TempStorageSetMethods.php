<?php

namespace App\IOData\DataMutators\Concerns;

trait TempStorageSetMethods
{
    protected ?\Illuminate\Filesystem\FilesystemAdapter $tempLocalStorage = null;
    protected ?string $tempFileName = null;

    public function setTempStorage(
        \Illuminate\Filesystem\FilesystemAdapter $tempLocalStorage,
        ?string $tempFileName = null
    ): static {
        $this->tempLocalStorage = $tempLocalStorage;

        if ($tempFileName) {
            $this->setTempFileName($tempFileName);
        }

        return $this;
    }

    public function getTempStorage(): ?\Illuminate\Filesystem\FilesystemAdapter
    {
        return $this->tempLocalStorage;
    }

    public function setTempFileName(string $tempFileName): static
    {
        $this->tempFileName = $tempFileName;

        return $this;
    }

    public function getTempFileFullPath(?string $tempFileName = null): ?string
    {
        if (!$this->getTempStorage()) {
            return null;
        }

        $fileName = $tempFileName ?: $this->tempFileName;

        if (!$fileName) {
            return null;
        }

        return $this->getTempStorage()?->path($fileName);
    }

    public function tempStorageIsValid(): bool
    {
        return $this->getTempStorage() && $this->getTempFileFullPath();
    }
}
