<?php

declare(strict_types=1);

use App\IOData\InputHandlers\LambdaRequest\EventHandler;

// $laravelPath = __DIR__ . '/../no-commit-old/laravel-app';

require_once __DIR__ . '/../utils/laravel-core.php';


$jsonResponse = function(array|string $body, int $status = 200, bool $bodyIsJson = false): string
{
    $bodyIsJson = is_string($body) && $bodyIsJson;

    $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Content-Type',
        'Access-Control-Allow-Methods' => 'OPTIONS,POST,GET'
    ];

    // Padrão de saída
    return json_encode([
        'statusCode' => $status,
        'headers' => $headers,
        'body' => $bodyIsJson ? json_decode($body) : $body,
    ], 64);
};

$handler = function(array $event) use($jsonResponse) : string
{
    try {
        echo __FILE__ . ':' . __LINE__ . PHP_EOL;
        echo __FUNCTION__ . PHP_EOL;

        $eventHandler = new EventHandler($event);

        $records = $eventHandler?->getRecords();

        $returnData = [
            'recordsCount' => count($records),
            'failCount' => 0,
            'successCount' => 0,
        ];

        foreach ($records as $recordData) {
            // WIP
            $processRecordReturnData = $eventHandler->processRecord(collect($recordData));

            if ($processRecordReturnData['fail'] ?? null) {
                $returnData['failCount'] = intval($returnData['failCount'] ?? 0) + 1;
            }

            if ($processRecordReturnData['success'] ?? null) {
                $returnData['successCount'] = intval($returnData['successCount'] ?? 0) + 1;
            }

            $returnData['recordsReturn'][] = $processRecordReturnData;
        }
    } catch (\Throwable $th) {
        throw $th;
    }

    return $jsonResponse(
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
};
