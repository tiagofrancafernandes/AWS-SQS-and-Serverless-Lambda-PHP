<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\IOData\DataMutators\Enums\RequestTypeEnum;
use App\Models\Interfaces\RequestModel;
use App\Models\Traits\RequestModelMethods;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportRequest extends Model implements RequestModel
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
        'sqs_message_id',
        'sqs_request_info',
        'sqs_message_body',
        'sqs_message_attributes',
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

    public function requestType(): RequestTypeEnum
    {
        return RequestTypeEnum::Export;
    }
}
