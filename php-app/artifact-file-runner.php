<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    stdoutLog(__FILE__ . ':' . __LINE__ . PHP_EOL);

    try {
        stdoutLog(__FILE__ . ':' . __LINE__);
        stdoutLog(config('import-export.export.target_disk') == 'export');

        try {
            Storage::disk('export')->put('teste-lambda-runtime', date('c')); // TODO: remover [é para teste S3]
        } catch (\Throwable $th) {
            stdoutLog(
                $errorMessage = spf(
                    'Error: %s %sLine: %s',
                    $th->getLine(),
                    PHP_EOL,
                    $th->getMessage(),
                )
            );

            return jsonResponse([
                'error' => $errorMessage ?? null,
            ], 422);
        }

        $record = $event['Records'][0] ?? [];
        $messageAttributes = recordAttributes($record);

        if (!$messageAttributes) {
            return jsonResponse([
                'error' => 'Empty [messageAttributes].'
            ], 422);
        }

        stdoutLog(__FILE__ . ':' . __LINE__);

        try {
            /**
             * @var \Illuminate\Contracts\Filesystem\Filesystem $s3Disk
             */
            $s3Disk = Storage::disk(config('import-export.export.target_disk'));
        } catch (\Throwable $th) {
            stdoutLog(
                $errorMessage = spf(
                    'Error: %s %sLine: %s',
                    $th->getLine(),
                    PHP_EOL,
                    $th->getMessage(),
                )
            );

            return jsonResponse([
                'error' => $errorMessage ?? null,
            ], 422);
        }

        stdoutLog(__FILE__ . ':' . __LINE__);

        $artifactFilePathOnS3 = recordAttribute($record, 'artifactFilePathOnS3')
            ?? env('ARTIFACT_FILE_PATH_ON_S3');

        if ($artifactFilePathOnS3) {
            stdoutLog(__FILE__ . ':' . __LINE__);

            printLine('artifactFilePathOnS3: ' . $artifactFilePathOnS3);

            if (
                !$artifactFilePathOnS3 || !$s3Disk?->exists($artifactFilePathOnS3)
            ) {
                stdoutLog(__FILE__ . ':' . __LINE__);

                return jsonResponse([
                    'message' => 'Invalid or not found artifactFilePathOnS3.',
                    'artifactFilePathOnS3' => $artifactFilePathOnS3,
                ], 500);
            }

            try {
                stdoutLog(__FILE__ . ':' . __LINE__);

                $localArtifactFilePath = Storage::disk('tmp')?->path(str()->random(15) . '.php');
                $content = $s3Disk?->get($artifactFilePathOnS3);

                if (
                    !$content || !file_put_contents(
                        $localArtifactFilePath,
                        $content
                    )
                ) {
                    stdoutLog(__FILE__ . ':' . __LINE__);

                    $message = 'Fail to [get/save] artifact file.';

                    return jsonResponse(compact('message'), 500);
                }
            } catch (\Throwable $th) {
                stdoutLog(
                    $errorMessage = spf(
                        'Error: %s %sLine: %s',
                        $th->getLine(),
                        PHP_EOL,
                        $th->getMessage(),
                    )
                );

                return jsonResponse([
                    'error' => $errorMessage ?? null,
                ], 422);
            }
        }

        try {
            stdoutLog(__FILE__ . ':' . __LINE__);

            $localArtifactFilePath = ($localArtifactFilePath ?? null) ?: 'php-app/artifacts/main-artifact-file.php';

            if (!is_file($localArtifactFilePath)) {
                stdoutLog(__FILE__ . ':' . __LINE__);

                $message = 'Fail to load artifact file.';

                return jsonResponse(compact('message'), 500);
            }

            $laravelPath = __DIR__ . '/../laravel-app/';

            $handler = include $localArtifactFilePath;

            $result = $handler && is_a($handler, Closure::class) ? $handler($event) : null;

            if (is_file($localArtifactFilePath) && $localArtifactFilePath != 'php-app/artifacts/main-artifact-file.php') {
                stdoutLog(__FILE__ . ':' . __LINE__);

                unlink($localArtifactFilePath);
            }

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
            stdoutLog(__FILE__ . ':' . __LINE__);

            stdoutLog(
                $errorMessage = spf(
                    'Error: %s %sLine: %s',
                    $th->getLine(),
                    PHP_EOL,
                    $th->getMessage(),
                )
            );

            return jsonResponse([
                'error' => $errorMessage ?? null,
            ], 422);
        }
    } catch (\Throwable $th) {
        return jsonResponse([
            'error' => 'Falha em ' . $th->getFile() . ':' . $th->getLine(),
            'catch_on' => __FILE__ . ':' . __LINE__,
            'errorMessage' => $th->getMessage(),
            'event' => $event,
        ]);
    }
}

printLine(handler(['isFinal' => true]));
