<?php

namespace App\Enums;

use TiagoF2\Enums\Core\Enum;

class IORequestStatusEnum extends Enum
{
    const CREATED = 05;
    const INITIALIZED = 10;
    const BEFORE_BEFORE_STEP_RUN = 15;
    const BEFORE_STEP_RUNNING = 20;
    const BEFORE_AFTER_STEP_RUN = 25;
    const AFTER_STEP_RUNNING = 30;
    const FINISHED = 35;
    const FINISHED_WITH_FAIL = 40;
    const FAIL = 45;
    const CANCELLED = 50;
    const UNDEFINED = 55;
    const HANDLE_FINISHED = 60;
    const HANDLE_BEFORE = 65;
    const HANDLE_SUCCESS = 70;
    const HANDLE_FAIL = 75;
    const GENERIC_SUCCESS = 100;

    protected static array $enums = [
        self::CREATED => 'CREATED',
        self::INITIALIZED => 'INITIALIZED',
        self::BEFORE_BEFORE_STEP_RUN => 'BEFORE_BEFORE_STEP_RUN',
        self::BEFORE_STEP_RUNNING => 'BEFORE_STEP_RUNNING',
        self::BEFORE_AFTER_STEP_RUN => 'BEFORE_AFTER_STEP_RUN',
        self::AFTER_STEP_RUNNING => 'AFTER_STEP_RUNNING',
        self::FINISHED => 'FINISHED',
        self::FINISHED_WITH_FAIL => 'FINISHED_WITH_FAIL',
        self::FAIL => 'FAIL',
        self::CANCELLED => 'CANCELLED',
        self::UNDEFINED => 'UNDEFINED',
        self::HANDLE_FINISHED => 'HANDLE_FINISHED',
        self::HANDLE_BEFORE => 'HANDLE_BEFORE',
        self::HANDLE_SUCCESS => 'HANDLE_SUCCESS',
        self::HANDLE_FAIL => 'HANDLE_FAIL',
        self::GENERIC_SUCCESS => 'GENERIC_SUCCESS',
    ];

    public static function finishedStatusList(): array
    {
        return [
            self::FINISHED,
            self::FINISHED_WITH_FAIL,
            self::FAIL,
            self::CANCELLED,
        ];
    }

    public static function notSuccessStatusList(): array
    {
        return [
            self::FAIL,
            self::CANCELLED,
            self::UNDEFINED,
            self::HANDLE_FAIL,
            self::FINISHED_WITH_FAIL,
        ];
    }
}
