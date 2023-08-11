<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

require __DIR__ . '/cli-common/helpers.php';

try {
    $artifactFilePathOnS3 = 'php-app/artifacts/main-artifact-file.php';

    $fileToUpload = $argv[1] ?? base_path('../php-app/artifacts/main-artifact-file.php');

    if (!$fileToUpload || !is_file($fileToUpload)) {
        quit('Invalid artifact file.', 5);
    }

    $disk = Storage::disk(config('import-export.export.target_disk'));

    $saved = $disk?->put(
        $artifactFilePathOnS3,
        file_get_contents($fileToUpload)
    );

    if (!$saved || !$disk->exists($artifactFilePathOnS3)) {
        throw new \Exception('Fail to upload artifact');
    }
} catch (\Throwable $th) {
    throw $th;
}
