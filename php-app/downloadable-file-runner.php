<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\InputHandlers\LambdaRequest\EventHandler;

// $laravelPath = __DIR__ . '/../no-commit-old/laravel-app';

require_once __DIR__ . '/../utils/laravel-core.php';

function printLine($content)
{
    print_r($content);
    echo PHP_EOL;
}

/**
 * @suppress _PHP0419
 * @suppress PHP0402
 * @suppress PHP1412
 */
function handler(array $event): string
{
    echo __FILE__ . ':' . __LINE__ . PHP_EOL;

    try {
        echo __FILE__ . ':' . __LINE__ . PHP_EOL;

        $record = ['Records'][0] ?? [];
        $messageAttributes = $record['messageAttributes'] ?? $record['MessageAttributes'] ?? [];

        if (!$messageAttributes) {
            return jsonResponse([
                'error' => 'Empty [messageAttributes].'
            ]);
        }

        $artifactFilePathOnS3 = $messageAttributes['artifactFilePathOnS3']['StringValue'] ?? 'php-app/downloadable-file.php';
        printLine('artifactFilePathOnS3: ' . $artifactFilePathOnS3);

        /**
         * @var \Illuminate\Contracts\Filesystem\Filesystem $s3Disk
         */
        $s3Disk = Storage::disk(config('import-export.export.target_disk'));

        if (
            !$artifactFilePathOnS3 || !$s3Disk?->exists($artifactFilePathOnS3)
        ) {
            return jsonResponse([
                'message' => 'Invalid artifactFilePathOnS3',
                'artifactFilePathOnS3' => $artifactFilePathOnS3,
            ], 500);
        }

        $localFilePathToSave = Storage::disk('tmp')?->path(str()->random(15) . '.php');
        $content = $s3Disk?->get($artifactFilePathOnS3);

        if (
            !$content || !file_put_contents(
                $localFilePathToSave,
                $content
            )
        ) {
            $message = 'Fail to [get/save] artifact file.';

            return jsonResponse(compact('message'), 500);
        }

        if (!is_file($localFilePathToSave)) {
            $message = 'Fail to load artifact file.';

            return jsonResponse(compact('message'), 500);
        }

        $laravelPath = __DIR__ . '/../laravel-app/';

        include "{$localFilePathToSave}";

        $result = isset($handler) && is_a($handler, Closure::class) ? $handler($event) : null;

        is_file($localFilePathToSave) && unlink($localFilePathToSave);

        $result = $result ?? 'empty-result';

        return jsonResponse(
            [
                'result' => $result,
                'php_version' => PHP_VERSION,
                '__FILE__' => __FILE__ . ':' . __LINE__,
                '__FUNCTION__' => __FUNCTION__,
            ]
        );
    } catch (\Throwable $th) {
        return jsonResponse([
            'error' => 'Falha em ' . __FILE__ . ':' . __LINE__,
            'errorLine' => $th->getLine(),
            'errorMessage' => $th->getMessage(),
            'event' => $event,
        ]);
    }
}

function jsonResponse(array|string $body, int $status = 200, bool $bodyIsJson = false): string
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
    ]);
}

handler([]);
