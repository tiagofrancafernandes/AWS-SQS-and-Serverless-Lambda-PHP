<?php

use App\Helpers\Tenancy\TenantRunner;
use App\Models\Tenant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('run:temp', function () {
    dump((new \App\IOData\DataMutators\Exporters\UserExporter(
        (new \App\IOData\DataMutators\RequestInfo\RequestInfo(
            $exportRequest = \App\Models\ExportRequest::factory()->createOne([
                'mapped_columns' => [
                    'id' => 'ID',
                    'name' => 'Nome',
                    'creator' => 'Conta criada por',
                ],
                'modifiers' => [
                    # Demo
                    // ['where', ['id', '>=', 3335]],
                    // serialize(['whereIn', ['id', [3335, 3336, 3337]]]),
                    serialize(['whereIn', ['id', [3335, 3336, 3337]]]),
                ],
            ])
        ))
    ))
        ->debug(false)->runProcess()->getLastRunReturn());

    // dump($exportRequest->toArray(), $exportRequest?->getFinalFileUrl());
    dump($exportRequest->{'id'});
    dump(
        spf(
            'ReportUrl: %s | FinalFileUrl: %s',
            $exportRequest->getReportUrl(),
            $exportRequest->getFinalFileUrl(),
        )
    );
})->purpose('Run temp script');

Artisan::command('run:temp:tenant:scope', function () {
    // Limpa a conexão atual do gerenciador de conexão de banco de dados

    // dump(\DB::table('products')->count()); // Erro

    $tenantId = 'catupiry';
    $tenant = Tenant::find($tenantId);
    $out = TenantRunner::make(compact('tenantId', 'tenant'))->run( // Sucesso
        $tenant,
        function (TenantRunner $runner) {
            dump(
                $runner->getData()
            );

            return \DB::table('products')->count();
        }
    );
    dump('out:', $out);

    // sleep(1);
    // dump(\DB::table('products')->count()); // Erro
})->purpose('Temp run');


Artisan::command('run:temp:sql:bind', function () {
    /**
     * Teste que surgiu de uma sugestão do Michel
     * Analisando a possibilidade para não ter que criar models no exportador
     */
    ini_set('memory_limit', '300M'); // Para testar as possibilidades

    // setDefaultConnection

    // $sql = 'SELECT * FROM users WHERE id = ?';
    // $bindings = [1];

    $sql = 'SELECT * FROM users';
    $bindings = [];
    echo 'Used memory: ' . (memory_get_peak_usage(true)/1024/1024) . PHP_EOL;

    collect(\Illuminate\Support\Facades\DB::select($sql, $bindings))
        ->each(function ($user) {
            if ($user->id == 1) {
                echo 'Used memory: ' . (memory_get_peak_usage(true)/1024/1024) . PHP_EOL;
            }
            // dd($user);
            return;
        });
        echo 'Used memory: ' . (memory_get_peak_usage(true)/1024/1024) . PHP_EOL;

    // SimpleExcelWriter::create

})->purpose('Temp run sql bind');
