<?php

use Illuminate\Foundation\Inspiring;
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
