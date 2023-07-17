<?php
declare(strict_types = 1);

use App\Helpers\File\FileHelpers;
use App\Helpers\Strings\StringHelpers;

if (!function_exists('logAndDump')) {
    /**
     * logAndDump function
     *
     * @param mixed ...$content
     * @return void
     */
    function logAndDump(...$content)
    {
        FileHelpers::logAndDump(...$content);
    }
}

if (!function_exists('logAndDumpSpf')) {
    /**
     * logAndDumpSpf function
     *
     *
    * @param string $firstString
    * @param float|int|string ...$params
    *
    * @return void
     */
    function logAndDumpSpf(string $firstString, float|int|string ...$params): void
    {
        $params = array_values($params);

        foreach ($params as $key => $item) {
            $params[$key] = trim(var_export($item, true), "'");
        }

        FileHelpers::logAndDump(sprintf($firstString, ...$params));
    }
}

if (!function_exists('getMimeType')) {
    /**
     * getMimeType function
     *
     * @param string|null $filePath
     * @return string|boolean|null
     */
    function getMimeType(?string $filePath): string|bool|null
    {
        return FileHelpers::getMimeType($filePath);
    }
}

if (!function_exists('isFilledFile')) {
    /**
     * isFilledFile function
     *
     * @param string|null $filePath
     *
     * @return boolean
     */
    function isFilledFile(?string $filePath): bool
    {
        return FileHelpers::isFilledFile($filePath);
    }
}

if (!function_exists('relativePath')) {
    /**
     * relativePath function
     *
     * @param string $filePath
     * @return string
     */
    function relativePath(string $filePath): string
    {
        return FileHelpers::relativePath($filePath);
    }
}

if (!function_exists('currentFileAndLine')) {
    /**
     * currentFileAndLine function
     *
     * @param boolean $relativePath
     *
     * @return string
     */
    function currentFileAndLine(bool $relativePath = false): string
    {
        return FileHelpers::currentFileAndLine($relativePath);
    }
}

if (!function_exists('testeParams')) {
        /**
     * formatDebugBacktrace function
     *
     * @param array $trace
     * @param boolean $relativePath
     *
     * @return string
     */
    function formatDebugBacktrace(array $trace, bool $relativePath = false): string
    {
        return FileHelpers::formatDebugBacktrace($trace, $relativePath);
    }
}

if (!function_exists('spf')) {

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
    function spf(string $firstString, float|int|string ...$params): string
    {
        return StringHelpers::spf($firstString, ...$params);
    }
}
