<?php

return [
    'debug' => boolval(
        env('IMPORT_EXPORT_DEBUG', env('APP_DEBUG', false))
    ),
    'export' => [
        // local|public|tmp|s3...
        'temp_disk' => env('EXPORT_TEMP_DISK', 'tmp'),
        'target_disk' => env('EXPORT_TARGET_DISK', 'public'),
        'report_local_disk' => env('EXPORT_REPORT_LOCAL_DISK', 'tmp'),
        'report_final_disk' => env('EXPORT_REPORT_FINAL_DISK', 'public'),
    ],

    'import' => [
        //
    ],
    'lambda_process' => [
        'throw_on_error_level' => env('IMPORT_EXPORT_THROW_ON_LAMBDA_PROCESS_ERROR_LEVEL', 'critical'),

        'throw_on_lambda_process' => boolval(
            env('IMPORT_EXPORT_THROW_ON_LAMBDA_PROCESS', false)
        ),
    ],
];
