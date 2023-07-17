<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\IOData\DataMutators\Enums\RequestTypeEnum;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_name',
        'tenant_id',
        'mapped_columns',
        'modifiers',
        'request_date',
        'report_file_url',
        'final_file_url',
        'was_finished_successfully',
        'status',
        'final_status',
        'log',
        'disk_name',
        'was_finished',
    ];

    protected $casts = [
        'mapped_columns' => AsCollection::class,
        'modifiers' => AsCollection::class,
        'request_date' => 'datetime',
        'was_finished_successfully' => 'boolean',
        'was_finished' => 'boolean',
    ];

    public function requestType(): RequestTypeEnum
    {
        return RequestTypeEnum::Export;
    }
}
