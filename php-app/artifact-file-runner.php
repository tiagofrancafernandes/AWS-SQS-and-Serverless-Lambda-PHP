<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use App\Helpers\Array\ArrayHelpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\InputHandlers\LambdaRequest\EventHandler;

require __DIR__ . '/cli-common/helpers.php';

/**
 * @suppress _PHP0419
 * @suppress PHP0402
 * @suppress PHP1412
 */
function handler(array $event): string
{
    $isFinal = $event['isFinal'] ?? false;

    if ($isFinal === true) {
        return jsonResponse(['finishedOn' => date('c')]);
    }

    echo __FILE__ . ':' . __LINE__ . PHP_EOL;

    try {
        echo __FILE__ . ':' . __LINE__ . PHP_EOL;

        $record = $event['Records'][0] ?? [];
        $messageAttributes = recordAttributes($record);

        if (!$messageAttributes) {
            return jsonResponse([
                'error' => 'Empty [messageAttributes].'
            ]);
        }

        /**
         * @var \Illuminate\Contracts\Filesystem\Filesystem $s3Disk
         */
        $s3Disk = Storage::disk(config('import-export.export.target_disk'));

        $artifactFilePathOnS3 = recordAttribute($record, 'artifactFilePathOnS3')
            ?? env('ARTIFACT_FILE_PATH_ON_S3');

        if ($artifactFilePathOnS3) {
            printLine('artifactFilePathOnS3: ' . $artifactFilePathOnS3);

            if (
                !$artifactFilePathOnS3 || !$s3Disk?->exists($artifactFilePathOnS3)
            ) {
                return jsonResponse([
                    'message' => 'Invalid or not found artifactFilePathOnS3.',
                    'artifactFilePathOnS3' => $artifactFilePathOnS3,
                ], 500);
            }

            $localArtifactFilePath = Storage::disk('tmp')?->path(str()->random(15) . '.php');

            $content = $s3Disk?->get($artifactFilePathOnS3);

            if (
                !$content || !file_put_contents(
                    $localArtifactFilePath,
                    $content
                )
            ) {
                $message = 'Fail to [get/save] artifact file.';

                return jsonResponse(compact('message'), 500);
            }
        }

        $localArtifactFilePath = $localArtifactFilePath ?: 'php-app/artifacts/main-artifact-file.php';

        if (!is_file($localArtifactFilePath)) {
            $message = 'Fail to load artifact file.';

            return jsonResponse(compact('message'), 500);
        }

        $laravelPath = __DIR__ . '/../laravel-app/';

        $handler = include $localArtifactFilePath;

        $result = $handler && is_a($handler, Closure::class) ? $handler($event) : null;

        is_file($localArtifactFilePath) && unlink($localArtifactFilePath);

        $result = ($result ?? null) ?: 'empty-result';

        return jsonResponse(
            [
                'result' => ArrayHelpers::stringToArray($result) ?: $result,
                'artifactFilePathOnS3' => $artifactFilePathOnS3,
                'php_version' => PHP_VERSION,
                '__FILE__' => __FILE__ . ':' . __LINE__,
                '__FUNCTION__' => __FUNCTION__,
                'event' => $event,
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

printLine(handler(['isFinal' => true]));
