<?php

namespace App\Models\Interfaces;

use App\IOData\DataMutators\Enums\RequestTypeEnum;

interface RequestModel
{
    public function requestType(): RequestTypeEnum;
    public function getMappedColumns(): \Illuminate\Support\Collection;
    public function getModifiers(): \Illuminate\Support\Collection;
    public function getResourceName(): string;
    public function getTenantId(): ?string;
    public function getRequestDate(): ?\Illuminate\Support\Carbon;
    public function wasFinished(): bool;
    public function setAsFinished(int $finalStatus): bool;
    public function setReportFile(string $reportFilePath, string $reportFileDisk,): mixed;
    public function getReportUrl(bool $s3DownloadMode = false): ?string;
    public function getFinalFileUrl(bool $s3DownloadMode = false): ?string;
}
