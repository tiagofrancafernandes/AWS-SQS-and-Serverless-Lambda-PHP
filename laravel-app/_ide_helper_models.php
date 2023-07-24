<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\ExportRequest
 *
 * @property int $id
 * @property string $resource_name
 * @property \Illuminate\Database\Eloquent\Casts\AsCollection $mapped_columns
 * @property string|null $tenant_id
 * @property \Illuminate\Database\Eloquent\Casts\AsCollection|null $modifiers
 * @property \Illuminate\Support\Carbon|null $request_date
 * @property string|null $report_file_path
 * @property string|null $report_file_disk
 * @property string|null $final_file_path
 * @property string|null $final_file_disk
 * @property bool|null $was_finished_successfully
 * @property int|null $status
 * @property int|null $final_status
 * @property string|null $log
 * @property string|null $disk_name
 * @property bool $was_finished
 * @property string|null $sqs_message_id
 * @property \Illuminate\Database\Eloquent\Casts\AsCollection|null $sqs_request_info
 * @property string|null $sqs_message_body
 * @property \Illuminate\Database\Eloquent\Casts\AsCollection|null $sqs_message_attributes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\ExportRequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereDiskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereFinalFileDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereFinalFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereFinalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereMappedColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereModifiers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereReportFileDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereReportFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereRequestDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereResourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereSqsMessageAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereSqsMessageBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereSqsMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereSqsRequestInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereWasFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportRequest whereWasFinishedSuccessfully($value)
 */
	class ExportRequest extends \Eloquent implements \App\Models\Interfaces\RequestModel {}
}

namespace App\Models{
/**
 * App\Models\ImportRequest
 *
 * @property int $id
 * @property string $resource_name
 * @property mixed $mapped_columns
 * @property string|null $tenant_id
 * @property mixed|null $modifiers
 * @property \Illuminate\Support\Carbon|null $request_date
 * @property string|null $report_file_path
 * @property string|null $report_file_disk
 * @property string|null $final_file_path
 * @property string|null $final_file_disk
 * @property bool|null $was_finished_successfully
 * @property int|null $status
 * @property int|null $final_status
 * @property string|null $log
 * @property string|null $disk_name
 * @property bool $was_finished
 * @property string $import_file_url
 * @property string $import_file_disk_name
 * @property string|null $sqs_message_id
 * @property mixed|null $sqs_request_info
 * @property string|null $sqs_message_body
 * @property mixed|null $sqs_message_attributes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\ImportRequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereDiskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereFinalFileDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereFinalFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereFinalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereImportFileDiskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereImportFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereMappedColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereModifiers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereReportFileDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereReportFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereRequestDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereResourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereSqsMessageAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereSqsMessageBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereSqsMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereSqsRequestInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereWasFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportRequest whereWasFinishedSuccessfully($value)
 */
	class ImportRequest extends \Eloquent implements \App\Models\Interfaces\RequestModel {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $creations
 * @property-read int|null $creations_count
 * @property-read User|null $creator
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

