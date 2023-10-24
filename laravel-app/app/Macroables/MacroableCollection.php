<?php

namespace App\Macroables;

use Closure;
use Illuminate\Support\Arr;

class MacroableCollection
{
    public static function dotGet(
        ?string $macroKey = 'dotGet',
        bool $onlyClosure = false,
    ): array|Closure {
        $macro = [
            $macroKey,
            function ($key) {
                return Arr::get(tap($this)->all(), $key);
            }
        ];

        return $onlyClosure ? $macro[1] : $macro;
    }
}
