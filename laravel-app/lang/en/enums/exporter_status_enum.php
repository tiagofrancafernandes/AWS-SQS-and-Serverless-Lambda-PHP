<?php

/**
 * Usage:
 *
 * By enum:
 * \App\Enums\ExporterStatusEnum::getValue(1, true, 'en');
 *
 * By key:
 * \App\Enums\ExporterStatusEnum::trans('some_here', 'en');
 */

return [
    'initialized' => 'initialized',
    'before_run' => 'before_run',
    'before_running' => 'before_running',
    'after_run' => 'after_run',
    'after_running' => 'after_running',
    'finished' => 'finished',
    'finished_with_fail' => 'finished_with_fail',
    'fail' => 'fail',
    'cancelled' => 'cancelled',
    'undefined' => 'undefined',
    'handle_finished' => 'handle_finished',
    'handle_success' => 'handle_success',
    'handle_fail' => 'handle_fail',
];
