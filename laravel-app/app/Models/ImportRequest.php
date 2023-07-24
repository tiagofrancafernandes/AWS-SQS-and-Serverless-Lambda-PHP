<?php

namespace App\Models;

use App\Models\Traits\RequestModelMethods;
use App\Models\Interfaces\RequestModel;
use Illuminate\Database\Eloquent\Model;
use App\IOData\DataMutators\Enums\RequestTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportRequest extends Model implements RequestModel
{
    use HasFactory;
    use RequestModelMethods;

    protected $fillable = [
        'resource_name',
        'tenant_id',
        'mapped_columns',
        'modifiers',
        'request_date',
        'report_file_path',
        'report_file_disk',
        'final_file_path',
        'final_file_disk',
        'was_finished_successfully',
        'status',
        'final_status',
        'log',
        'disk_name',
        'was_finished',
        'import_file_url',
        'import_file_disk_name',
        'sqs_message_id',
        'sqs_request_info',
        'sqs_message_body',
        'sqs_message_attributes',
        'user_id_type',
        'user_id',
    ];

    protected $casts = [
        'mapped_columns' => AsCollection::class,
        'modifiers' => AsCollection::class,
        'request_date' => 'datetime',
        'was_finished_successfully' => 'boolean',
        'was_finished' => 'boolean',
        'sqs_request_info' => AsCollection::class,
        'sqs_message_attributes' => AsCollection::class,
    ];

    protected $appends = [
        'statusName',
        'requestType',
        'requestTypeEnum',
    ];

    public function requestType(): RequestTypeEnum
    {
        return RequestTypeEnum::Import;
    }

    public function getImportFileUrl(): ?string
    {
        return $this->{'import_file_url'} ?? null;
    }

    public function getImportFileDiskName(): ?string
    {
        return $this->{'import_file_disk_name'} ?? null;
    }
}
