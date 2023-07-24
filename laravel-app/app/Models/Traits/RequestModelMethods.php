<?php

namespace App\Models\Traits;

use App\Enums\IORequestStatusEnum;
use App\IOData\DataMutators\Enums\RequestTypeEnum;

trait RequestModelMethods
{
    abstract public function requestType(): RequestTypeEnum;

    public function getMappedColumns(): \Illuminate\Support\Collection
    {
        return $this->{'mapped_columns'} ?? collect();
    }

    public function getModifiers(): \Illuminate\Support\Collection
    {
        return $this->{'modifiers'} ?? collect();
    }

    public function getResourceName(): string
    {
        return $this->{'resource_name'} ?? '';
    }

    public function getTenantId(): ?string
    {
        return $this->{'tenant_id'} ?? null;
    }

    public function getRequestDate(): ?\Illuminate\Support\Carbon
    {
        $date = $this->{'request_date'} ?? $this->{'created_at'} ?? null;

        return $date ? \Illuminate\Support\Carbon::parse($date) : null;
    }

    public function getReportStorage(): ?\Illuminate\Filesystem\FilesystemAdapter
    {
        try {
            $diskName = $this->{'report_file_disk'} ?? null;

            if (!$diskName) {
                return null;
            }

            return \Illuminate\Support\Facades\Storage::disk($diskName) ?? null;
        } catch (\Throwable $th) {
            if (config('app.debug', false)) {
                throw $th;
            }

            \Log::error($th);
            return null;
        }
    }

    public function getReportUrl(bool $s3DownloadMode = false): ?string
    {
        try {
            $reportFilePath = $this->{'report_file_path'} ?? null;

            if (!$reportFilePath) {
                return null;
            }

            if (!is_object($this->getFinalFileStorage())) {
                return null;
            }

            $fileName = pathinfo($reportFilePath, PATHINFO_BASENAME);

            if (method_exists($this->getFinalFileStorage(), 'temporaryUrl')) {
                $options = $s3DownloadMode && $fileName ?
                    [
                        'ResponseContentType' => 'application/octet-stream',
                        'ResponseContentDisposition' => 'attachment; filename=' . $fileName,
                    ] : [];

                return $this->getFinalFileStorage()
                    ->temporaryUrl(
                        $reportFilePath,
                        now()->addDays(7),
                        $options
                    );
            }

            return $this->getReportStorage()?->url($reportFilePath) ?? null;
        } catch (\Throwable $th) {
            if (config('app.debug', false)) {
                throw $th;
            }

            \Log::error($th);
            return null;
        }
    }

    public function getFinalFileStorage(): ?\Illuminate\Filesystem\FilesystemAdapter
    {
        try {
            $diskName = $this->{'final_file_disk'} ?? null;

            if (!$diskName) {
                return null;
            }

            return \Illuminate\Support\Facades\Storage::disk($diskName) ?? null;
        } catch (\Throwable $th) {
            if (config('app.debug', false)) {
                throw $th;
            }

            \Log::error($th);
            return null;
        }
    }

    public function getFinalFileUrl(bool $s3DownloadMode = false): ?string
    {
        try {
            $finalFilePath = $this->{'final_file_path'} ?? null;

            if (!$finalFilePath) {
                return null;
            }

            if (!is_object($this->getFinalFileStorage())) {
                return null;
            }

            $fileName = pathinfo($finalFilePath, PATHINFO_BASENAME);

            if (method_exists($this->getFinalFileStorage(), 'temporaryUrl')) {
                $options = $s3DownloadMode && $fileName ?
                    [
                        'ResponseContentType' => 'application/octet-stream',
                        'ResponseContentDisposition' => 'attachment; filename=' . $fileName,
                    ] : [];

                return $this->getFinalFileStorage()
                    ->temporaryUrl(
                        $finalFilePath,
                        now()->addDays(7),
                        $options
                    );
            }

            return $this->getFinalFileStorage()?->url($finalFilePath) ?? null;
        } catch (\Throwable $th) {
            if (config('app.debug', false)) {
                throw $th;
            }

            \Log::error($th);
            return null;
        }
    }

    public function wasFinished(): bool
    {
        if ($this->{'final_status'} ?? null) {
            return true;
        }

        return in_array(
            $this->{'status'} ?? null,
            IORequestStatusEnum::finishedStatusList(),
            true
        );
    }

    public function setAsFinished(int $finalStatus): bool
    {
        if ($this->wasFinished()) {
            if (config('import-export.debug', false)) {
                $currentFinalStatus = $this->{'final_status'} ?? null;

                throw new \Exception(
                    sprintf(
                        'this request has already been finished. The final status is: [%s => %s]',
                        $currentFinalStatus,
                        IORequestStatusEnum::get($currentFinalStatus),
                    )
                );
            }

            return false;
        }

        $finalStatus = intval($finalStatus);

        if (!in_array(
            $finalStatus,
            IORequestStatusEnum::finishedStatusList(),
            true
        )) {
            if (config('import-export.debug', false)) {
                throw new \Exception(
                    sprintf(
                        'Invalid final status. Allowed values: [%s]',
                        implode(', ', IORequestStatusEnum::finishedStatusList())
                    )
                );
            }

            return false;
        }

        $asSuccessfully = IORequestStatusEnum::FINISHED === $finalStatus;

        return (bool) $this->update([
            'final_status' => $finalStatus,
            'was_finished_successfully' => $asSuccessfully,
        ]);
    }

    public function setReportFile(
        string $reportFilePath,
        string $reportFileDisk,
    ): mixed {
        if (!$reportFilePath || !$reportFileDisk) {
            return null;
        }

        if (!in_array($reportFileDisk, array_keys(config('filesystems.disks')))) {
            throw new \Exception('Invalid "reportFileDisk"', 1);
        }

        return $this->update([
            'report_file_path' => $reportFilePath,
            'report_file_disk' => $reportFileDisk,
        ]);
    }

    public function getStatusNameAttribute()
    {
        $status = $this->{'final_status'} ?? $this->{'status'} ?? null;

        if (!$status) {
            return null;
        }

        return IORequestStatusEnum::get($status);
    }

    public function getRequestTypeEnumAttribute()
    {
        return $this->{'requestType'}() ?? null;
    }

    public function getRequestTypeAttribute()
    {
        return ($this->{'requestType'}() ?? null)?->name ?? null;
    }
}
