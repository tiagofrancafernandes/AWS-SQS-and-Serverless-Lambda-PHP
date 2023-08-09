<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\IOData\DB\LambdaRuntimeDatabase;
use App\IOData\InputHandlers\LambdaRequest\EventHandler;

// $laravelPath = __DIR__ . '/../no-commit-old/laravel-app';

require_once __DIR__ . '/../utils/laravel-core.php';

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

$artifactFilePathOnS3 = 'php-app/downloadable-file.php';

$saved = Storage::disk(config('import-export.export.target_disk'))?->put(
    $artifactFilePathOnS3,
    file_get_contents(base_path('../php-app/downloadable-file.php'))
);

die(
    var_export($saved, true)
);
