<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Inspiring;
use App\Helpers\Tenancy\TenantRunner;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\DataMutators\RequestInfo\RequestInfo;

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

    sleep(1);
    dump(\DB::table('products')->count()); // Erro
})->purpose('Temp run');

Artisan::command('run:temp:tenant:initialize', function () {
    // Limpa a conexão atual do gerenciador de conexão de banco de dados

    // dump(\DB::table('products')->count()); // Erro

    $tenantId = 'catupiry';
    $tenant = Tenant::find($tenantId);
    TenantRunner::make()->initialize($tenant);
    $out = (fn () => \DB::table('products')->count())();
    dump('out:', $out);

    dump(\DB::table('products')->count()); // Sucesso

    TenantRunner::make()->end();
    sleep(1);
    dump(\DB::table('products')->count()); // Erro
})->purpose('Temp run');

Artisan::command('run:temp:sql:bind', function () {
    /**
     * Teste que surgiu de uma sugestão do Michel
     * Analisando a possibilidade para não ter que criar models no exportador
     */
    ini_set('memory_limit', '100M'); // Usado aqui para testar as possibilidades

    // setDefaultConnection

    // $sql = 'SELECT * FROM users WHERE id = ?';
    // $bindings = [1];

    $sql = 'SELECT * FROM users';
    $bindings = [];

    echo 'Used memory: ' . (memory_get_peak_usage(true) / 1024 / 1024) . PHP_EOL;

    $count = 0;
    // foreach (DB::cursor('select * from users where id in (?, ?, ?)', [1, 3, 4]) as $user) {
    foreach (DB::cursor($sql, $bindings) as $user) {
        // dd($user);
        $count++;
        echo $user?->id . ' | ';
    }

    // collect(DB::select($sql, $bindings))
    //     ->each(function ($user) {
    //         if ($user->id == 1) {
    //             echo 'Used memory: ' . (memory_get_peak_usage(true) / 1024 / 1024) . PHP_EOL;
    //         }
    //         // dd($user);
    //         return;
    //     });

    // dump(DB::select('select count(*) from users', []));

    echo 'Used memory: ' . (memory_get_peak_usage(true) / 1024 / 1024) . PHP_EOL;
    dd($count);

    // SimpleExcelWriter::create

})->purpose('Temp run sql bind');

Artisan::command('run:temp:query:builder', function () {
    $tenantId = 'catupiry';
    $tenant = Tenant::find($tenantId);
    TenantRunner::make()->initialize($tenant);

    $queryData = [
        'table' => 'products',
        'joins' => [
            ['product_brands', 'product_brands.id', '=', 'products.product_brand_id'],
        ],
        'select' => [
            'products.*',
            'product_brands.name as brand',
        ],
        'wheres' => [
            ['where', ['products.id', '=', 650]],
        ],
        'column_rename' => [
            'id' => 'ID',
            'uuid' => 'UUID',
            'name' => 'Name',
            'barcode' => 'barcode',
            'sku' => 'sku',
            'customer_sku' => 'customer_sku',
            'is_competitor' => 'is_competitor',
            'is_active' => 'is_active',
            'product_category_id' => 'product_category_id',
            'product_brand_id' => 'product_brand_id',
            'product_type_id' => 'product_type_id',
            'product_manufacturer_id' => 'product_manufacturer_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
            'brand' => 'Marca',
        ],
    ];

    $result1 = DB::table('products')
        ->where('products.id', 650)
        ->join('product_brands', 'product_brands.id', '=', 'products.product_brand_id')
        ->select('products.*', 'product_brands.name as brand')
        ->first();

    dump('result1:', $result1);

    $query = DB::table($queryData['table']);

    $query = $query->select($queryData['select'] ?? []);

    foreach ($queryData['wheres'] ?? [] as $where) {
        $method = $where[0] ?? null;

        if (!$method || !is_string($method)) {
            return null;
        }

        $methodParams = $where[1] ?? [];

        $query = $methodParams ? $query->{$method}(...$methodParams) : $query->{$method}();
    }

    foreach ($queryData['joins'] ?? [] as $joinParams) {
        $query = $query->join(...$joinParams);
    }

    $result2 = $query->first();

    dump('result2:', $result2);

    $result2Alter = new stdClass();

    $columnRename = $queryData['column_rename'] ?? [];

    foreach ($result2 as $key => $value) {
        $newKey = $columnRename[$key] ?? $key;
        $result2Alter->{$newKey} = $value;
    }

    dump('result2_ater:', $result2Alter);
})->purpose('Temp run sql bind');

Artisan::command('run:temp:custom:query:exporter', function () {
    $query = DB::table('products')
        ->where('products.id', 650)
        ->join('product_brands', 'product_brands.id', '=', 'products.product_brand_id')
        ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
        ->join('product_types', 'product_types.id', '=', 'products.product_type_id')
        ->join('product_manufacturers', 'product_manufacturers.id', '=', 'products.product_manufacturer_id')
        ->select(
            'products.*',
            'product_brands.name as brand',
            'product_categories.name as category',
            'product_types.name as type',
            'product_manufacturers.name as manufacturer',
        );
    // dd($query->toSql(), $query->getBindings());

    dump((new \App\IOData\DataMutators\Exporters\RawQueryExporter(
        (new RequestInfo(
            $exportRequest = RequestInfo::createRequestModel(
                'export',
                $exportRequestData = [
                    'resource_name' => 'raw_query_exporter',
                    'tenant_id' => null,
                    'tenant_id' => 'global',
                    'tenant_id' => 'catupiry',
                    'mapped_columns' => $mapped_columns = [
                        'id' => 'ID',
                        'uuid' => 'UUID',
                        'name' => 'Produto',
                        'barcode' => 'Código de barras',
                        'sku' => 'SKU',
                        'customer_sku' => 'SKU do cliente',
                        'is_competitor' => 'Concorrente',
                        'is_active' => 'Ativo',
                        'product_category_id' => 'ID da categoria',
                        'product_brand_id' => 'ID da marca',
                        'product_type_id' => 'ID do tipo',
                        'product_manufacturer_id' => 'ID do fabricante',
                        'created_at' => 'Criado em',
                        'updated_at' => 'Atualizado em',
                        'deleted_at' => 'Deletado em',
                        'brand' => 'Marca',
                        'category' => 'Categoria',
                        'type' => 'Tipo',
                        'manufacturer' => 'Fabricante',
                    ],
                    'modifiers' => $modifiers = [
                        # Demo
                        // ['where', ['id', '>=', 3335]],
                        // serialize(['whereIn', ['id', [3335, 3336, 3337]]]),
                        // serialize(['whereIn', ['id', [3335, 3336, 3337]]]),

                        'rawQuery' => [
                            'sql' => $query->toSql(),
                            'bindings' => $query->getBindings(),
                        ],
                    ],
                ]
            )
        ))
    ))
        ->debug(true)->runProcess()->getLastRunReturn());

    dd($exportRequestData);

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

Artisan::command('run:temp:memory_database', function () {
    print_r(
        LambdaRuntimeDatabase::setRuntimeDatabaseAs(
            'sqlite',
            true,
            true,
        )
    );
})->purpose('Display an inspiring quote');
