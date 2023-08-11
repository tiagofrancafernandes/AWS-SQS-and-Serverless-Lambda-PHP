<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

return function (array $event): string {
    try {
        return "Hello world!";
    } catch (\Throwable $th) {
        // throw $th;
        return spf('Error: ', $th->getMessage());
    }
};
