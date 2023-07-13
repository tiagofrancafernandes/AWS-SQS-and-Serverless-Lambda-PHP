<?php

use App\Helpers\File\FileHelpers;

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
     * function currentFileAndLine
     *
     * @return string
     */
    function currentFileAndLine(bool $relativePath = false): string
    {
        return FileHelpers::currentFileAndLine($relativePath);
    }
}
