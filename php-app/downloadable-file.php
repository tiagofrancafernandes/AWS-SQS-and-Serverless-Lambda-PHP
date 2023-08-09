<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\InputHandlers\LambdaRequest\EventHandler;

// $laravelPath = __DIR__ . '/../no-commit-old/laravel-app';

$laravelPath = $laravelPath ?? __DIR__;

require_once $laravelPath . '/../utils/laravel-core.php';

if (!function_exists('printLine')) {
    /**
     * function printLine
     *
     * @param mixed $content
     * @return void
     */

    function printLine($content): void
    {
        print_r($content);
        echo PHP_EOL;
    }
}


/**
 * @suppress PHP0419
 */
$handler = function (array $event) {
    $config = array_merge(
        config('database.connections.pgsql'),
        [
            'driver' => 'pgsql',
            'host' => 'database-1.cluster-cptrxtk4hveh.us-east-1.rds.amazonaws.com',
            'port' => 5432,
            'password' => 'p0stgre5',
            'database' => 'dev_grupogps_comptrade',
            /*
            'host' => '172.17.0.1',
            'port' => 8010,
            'password' => 'postgres',
            'database' => 'postgres',
            /***/
            'username' => 'postgres',
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
