<?php

namespace App\Helpers\Strings;

class StringHelpers
{
    /**
     * spf function  Easy way to use sprintf
     *
     *
     * ```php
     * spf('aa %s %d', 123, 34); // "aa 123 34"
     * ```
     *
     * @param string $firstString
     * @param float|int|string ...$params
     *
     * @return string
     */
    public static function spf(string $firstString, float|int|string ...$params): string
    {
        return sprintf($firstString, ...$params);
    }
}
