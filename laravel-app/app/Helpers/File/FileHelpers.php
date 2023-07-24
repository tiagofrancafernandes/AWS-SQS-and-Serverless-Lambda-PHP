<?php

namespace App\Helpers\File;

class FileHelpers
{
    /**
     * getMimeType function
     *
     * @param string|null $filePath
     * @return string|boolean|null
     */
    public static function getMimeType(?string $filePath): string|bool|null
    {
        if (!$filePath || !is_file($filePath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeType = finfo_file($finfo, $filePath);

        finfo_close($finfo);

        return $mimeType;
    }

    /**
     * isFilledFile function
     *
     * @param string|null $filePath
     *
     * @return boolean
     */
    public static function isFilledFile(?string $filePath): bool
    {
        $mimeType = static::getMimeType($filePath);

        if (!$mimeType) {
            return false;
        }

        return !in_array($mimeType, [
            'application/x-empty',
        ], true);
    }

    /**
     * relativePath function
     *
     * @param string $filePath
     * @return string
     */
    public static function relativePath(string $filePath): string
    {
        $fileFullPath = !str_contains($filePath, base_path()) ? base_path($filePath) : $filePath;

        return str_replace(base_path('/'), '', $fileFullPath);
    }

    /**
     * logAndDump function
     *
     * @param mixed ...$content
     * @return void
     */
    public static function logAndDump(...$content)
    {
        dump(...$content);
        \Log::info(var_export($content, true));
    }

    /**
     * currentFileAndLine function
     *
     * @param boolean $relativePath
     *
     * @return string
     */
    public static function currentFileAndLine(bool $relativePath = false): string
    {
        $trace = debug_backtrace();

        return static::formatDebugBacktrace($trace, $relativePath);
    }

    /**
     * formatDebugBacktrace function
     *
     * @param array $trace
     * @param boolean $relativePath
     *
     * @return string
     */
    public static function formatDebugBacktrace(array $trace, bool $relativePath = false): string
    {
        $caller = $trace[1] ?? null;

        $file = $caller['file'] ?? null;
        $line = $caller['line'] ?? null;

        $lineAndNumber = implode(
            ':',
            array_filter([
                $file,
                $line,
            ])
        );

        return $relativePath ? static::relativePath($lineAndNumber) : $lineAndNumber;
    }
}
