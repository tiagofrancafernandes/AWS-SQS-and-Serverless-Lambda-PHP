<?php

/**
 * Usage:
 *
 * By enum:
 * \App\Enums\IORequestStatusEnum::getValue(1, true, 'en');
 *
 * By key:
 * \App\Enums\IORequestStatusEnum::trans('some_here', 'en');
 */

return [
    'CREATED' => 'created',
    'INITIALIZED' => 'initialized',
    'BEFORE_BEFORE_STEP_RUN' => 'before_before_step_run',
    'BEFORE_STEP_RUNNING' => 'before_step_running',
    'BEFORE_AFTER_STEP_RUN' => 'before_after_step_run',
    'AFTER_STEP_RUNNING' => 'after_step_running',
    'AFTER_STEP_DONE' => 'after_step_done',
    'FINISHED' => 'finished',
    'FINISHED_WITH_FAIL' => 'finished_with_fail',
    'FAIL' => 'fail',
    'CANCELLED' => 'cancelled',
    'UNDEFINED' => 'undefined',
    'HANDLE_FINISHED' => 'handle_finished',
    'HANDLE_BEFORE' => 'handle_before',
    'HANDLE_SUCCESS' => 'handle_success',
    'HANDLE_FAIL' => 'handle_fail',
    'GENERIC_SUCCESS' => 'generic_success',
];
