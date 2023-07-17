<?php

$clearedStr = fn (string $str, string $replacer = '') => str_replace(
    [
        '___', '__', '_',
    ],
    $replacer,
    trim(preg_replace('/[\W]/', '_', trim($str)), '_')
);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            // 'endpoint' => env('AWS_ENDPOINT', 'https://minio:9000'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'import-export' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID_IMPORT_EXPORT'),
            'secret' => env('AWS_SECRET_ACCESS_KEY_IMPORT_EXPORT'),
            'region' => env('AWS_DEFAULT_REGION_IMPORT_EXPORT'),
            'bucket' => env('AWS_BUCKET_IMPORT_EXPORT'),
            'url' => env('AWS_URL_IMPORT_EXPORT'),
            'endpoint' => env('AWS_ENDPOINT_IMPORT_EXPORT'),
            // 'endpoint' => env('AWS_ENDPOINT_IMPORT_EXPORT', 'https://minio:9000'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT_IMPORT_EXPORT', false),
            'throw' => (bool) env('AWS_BUCKET_IMPORT_EXPORT_THROW', false),
        ],

        'import' => [
            'driver' => 'scoped',
            'disk' => 'import-export',
            'prefix' => 'import-files', // path/to/dir
        ],

        'export' => [
            'driver' => 'scoped',
            'disk' => 'import-export',
            'prefix' => 'export-files', // path/to/dir
        ],

        'ie_report' => [
            'driver' => 'scoped',
            'disk' => 'import-export',
            'prefix' => 'report-files', // path/to/dir
            'throw' => (bool) env('AWS_BUCKET_IMPORT_EXPORT_THROW', false),
        ],

        'tmp' => [
            'driver' => 'local',
            'root' => ((string) env('TEMP_DIR_BASE_PATH', sys_get_temp_dir())) . '/' .
                $clearedStr(sprintf('%s-%s', ...[
                    (string) env('APP_NAME', 'Laravel'),
                    (string) (env('APP_KEY') ?: 'temp-dir-jhkgjhkgjh'),
                ]), '-'),
            'url' => env('APP_URL') . '/tmp_storage',
            'visibility' => 'public',
            'throw' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
