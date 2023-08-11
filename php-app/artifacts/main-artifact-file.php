<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\InputHandlers\LambdaRequest\EventHandler;

/**
 * @suppress PHP0413
 */
return function (array $event): string {
    try {
        printLine(json_encode($eventComeInfo = [
            '__LINE__' => __FILE__ . ':' . __LINE__,
            'lastChangeOfThisFile' => date('c', filemtime(__FILE__)),
            '__FUNCTION__' => __FUNCTION__,
            'AWS_LAMBDA_FUNCTION_NAME' => env('AWS_LAMBDA_FUNCTION_NAME'),
            'AWS_LAMBDA_FUNCTION_VERSION' => env('AWS_LAMBDA_FUNCTION_VERSION'),
            'AWS_REGION' => env('AWS_REGION'),
            'eventComeOn' => date('c'),
        ], 64)) . PHP_EOL;

        printLine(json_encode(config('database'), 64));
        printLine(json_encode(config('filesystems'), 64));

        printLine(
            LambdaRuntimeDatabase::setRuntimeDatabaseAs(
                'sqlite',
                true,
                true,
            )
        );

        $eventHandler = new EventHandler($event);

        $records = $eventHandler?->getRecords();

        $returnData = [
            'recordsCount' => count($records),
            'failCount' => 0,
            'successCount' => 0,
        ];

        foreach ($records as $recordData) {
            $processRecordReturnData = $eventHandler->processRecord(collect($recordData));

            if ($processRecordReturnData['fail'] ?? null) {
                $returnData['failCount'] = intval($returnData['failCount'] ?? 0) + 1;
            }

            if ($processRecordReturnData['success'] ?? null) {
                $returnData['successCount'] = intval($returnData['successCount'] ?? 0) + 1;
            }

            $returnData['recordsReturn'][] = $processRecordReturnData;
        }

        return jsonResponse(
            array_merge(
                $returnData,
                [
                    '__FILE__' => __FILE__ . ':' . __LINE__,
                    '__FUNCTION__' => __FUNCTION__,
                    'app_name' => env('APP_NAME'),
                    'php_version' => PHP_VERSION,
                    'event' => $event,
                ]
            )
        );
    } catch (\Throwable $th) {
        throw $th;
    }
};
