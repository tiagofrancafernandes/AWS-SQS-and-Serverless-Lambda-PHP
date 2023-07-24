<?php

namespace Database\Seeders;

use App\Models\ExportRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExportRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExportRequest::factory(5)->create();
    }
}
