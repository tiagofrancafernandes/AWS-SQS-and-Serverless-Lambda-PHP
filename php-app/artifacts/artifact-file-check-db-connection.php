<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\InputHandlers\LambdaRequest\EventHandler;

return function (array $event): string {
    $config = array_merge(
        config('database.connections.comptrade_central'),
        [
            // 'driver' => 'pgsql',
            // 'host' => 'host-to-check',
            // 'port' => 5432,
            // 'password' => 'pass123',
            // 'database' => 'dev_grupogps_comptrade',
            // 'username' => 'postgres',
        ]
    );

    try {

        Config::set('database.connections.temp_pgsql', $config);

        DB::connection('temp_pgsql')->getPdo();

        return "Conexão com o banco de dados de teste estabelecida com sucesso!";
    } catch (\Throwable $th) {
        // throw $th;
        return spf('Error: %s | Config: %s', $th->getMessage(), http_build_query($config));
    }
};
