<?php

namespace App\Enums;

use TiagoF2\Enums\Core\Enum;

class ExporterStatusEnum extends Enum
{
    const STATUS_CREATED = 05;
    const STATUS_INITIALIZED = 10;
    const STATUS_BEFORE_RUN = 15;
    const STATUS_BEFORE_RUNNING  = 20;
    const STATUS_AFTER_RUN  = 25;
    const STATUS_AFTER_RUNNING  = 30;
    const STATUS_FINISHED  = 35;
    const STATUS_FINISHED_WITH_FAIL  = 40;
    const STATUS_FAIL  = 45;
    const STATUS_CANCELLED  = 50;
    const STATUS_UNDEFINED  = 55;
    const HANDLE_FINISHED  = 60;
    const HANDLE_BEFORE  = 65;
    const HANDLE_SUCCESS  = 70;
    const HANDLE_FAIL  = 75;

    protected static array $enums = [
        self::STATUS_CREATED => 'created',
        self::STATUS_INITIALIZED => 'initialized',
        self::STATUS_BEFORE_RUN => 'before_run',
        self::STATUS_BEFORE_RUNNING => 'before_running',
        self::STATUS_AFTER_RUN => 'after_run',
        self::STATUS_AFTER_RUNNING => 'after_running',
        self::STATUS_FINISHED => 'finished',
        self::STATUS_FINISHED_WITH_FAIL => 'finished_with_fail',
        self::STATUS_FAIL => 'fail',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_UNDEFINED => 'undefined',
        self::HANDLE_FINISHED => 'handle_finished',
        self::HANDLE_BEFORE => 'handle_before',
        self::HANDLE_SUCCESS => 'handle_success',
        self::HANDLE_FAIL => 'handle_fail',
    ];
}
