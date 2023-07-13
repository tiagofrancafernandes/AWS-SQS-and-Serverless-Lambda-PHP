<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $factoryCount = env('FACTORY_COUNT');
        $factoryCountCreate = is_numeric($factoryCount) && $factoryCount >= 1 ? (int) $factoryCount : 500;

        echo "Creating {$factoryCountCreate} records" . PHP_EOL;
        \App\Models\User::factory($factoryCountCreate)->create();
    }
}
