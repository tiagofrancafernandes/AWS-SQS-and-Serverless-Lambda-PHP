<?php

namespace Database\Factories;

use App\Enums\IORequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExportRequest>
 */
class ExportRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resource_name' => 'tenant_users',
            'tenant_id' => null,
            'mapped_columns' => [
                'id' => 'ID',
                'name',
                'creator' => 'Conta criada por',
            ],
            'modifiers' => [
                // serialize(['where', ['id', '=', 2]]),
                // serialize(['whereIn', ['id', [2, 4, 6]]]),
                serialize(['whereIn', ['id', [3335, 3336, 3337]]]),
                // ['whereIn', ['id', [3335, 3336, 3337]]],
                // serialize(['orderBy', ['id', 'desc']]),
                // serialize(['whereNot', ['id', 3812]]),
            ],
            'request_date' => now(),
            'report_file_path' => null,
            'report_file_disk' => null,
            'final_file_path' => null,
            'final_file_disk' => null,
            'was_finished_successfully' => null,
            'status' => IORequestStatusEnum::CREATED,
            'final_status' => null,
            'log' => null,
            'disk_name' => null,
            'was_finished' => false,
            'user_id_type' => null,
            'user_id' => null,
        ];
    }
}
