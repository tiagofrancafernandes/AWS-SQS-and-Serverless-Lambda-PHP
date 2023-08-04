<?php

namespace App\IOData\DB;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class LambdaRuntimeDatabase
{
    public static function setRuntimeDatabaseAs(
        ?string $connectionName = null,
        ?bool $runMigrations = null,
        ?bool $runMigrationsOnSteps = null,
    ) {
        if (!config('database.runtime_database.allowed_to_set')) {
            return false;
        }

        $connectionName = $connectionName?: config('database.runtime_database.connection_name');
        $runMigrations = $runMigrations?: config('database.runtime_database.run_migrations');
        $runMigrationsOnSteps = $runMigrationsOnSteps?: config('database.runtime_database.run_migrations_on_steps');

        if (app()->environment('production')) {
            if (!config('database.runtime_database.run_in_prod')) {
                return null;
            }
        }

        $runArtisanCommand = function ($command, $parameters = [], $outputBuffer = null) {
            try {
                \Illuminate\Support\Facades\Artisan::call($command, $parameters, $outputBuffer);

                return \Illuminate\Support\Facades\Artisan::output();
            } catch (\Throwable $th) {
                \Log::error($th);
                return json_encode([
                    'success' => false,
                    'error' => $th->getMessage(),
                ], 64);
            }
        };

        $connectionName = $connectionName ?: 'sqlite_memory';

        if ($connectionName == 'sqlite') {
            $sqliteFilePath = Storage::disk('tmp')->path('database/database.sqlite');

            print_r(['sqliteFilePath' => $sqliteFilePath]);

            Config::set('database.connections.sqlite.database', $sqliteFilePath);

            Config::set('logging.default', 'stderr');

            dump(Storage::disk('tmp')->exists('database/database.sqlite'));

            if (Storage::disk('tmp')->exists('database/database.sqlite')) {
                Storage::disk('tmp')->delete('database/database.sqlite');
            }

            dump(Storage::disk('tmp')->exists('database/database.sqlite'));

            if (!Storage::disk('tmp')->exists('database/database.sqlite')) {
                Storage::disk('tmp')->put('database/database.sqlite', '');
            }

            dump(Storage::disk('tmp')->exists('database/database.sqlite'));
        }

        DB::setDefaultConnection($connectionName);
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        if ($runMigrations) {
            $migrationOutput = $runArtisanCommand('migrate', ['--step' => $runMigrationsOnSteps]);

            print_r('Running migration...');
            print_r($migrationOutput);

            User::factory()->createOne();

            print_r(['count:', User::count()]);
            print_r(['first:', User::first()->toArray()]);
        }

        dump(DB::getDefaultConnection());

        return DB::getDefaultConnection();
    }
}
