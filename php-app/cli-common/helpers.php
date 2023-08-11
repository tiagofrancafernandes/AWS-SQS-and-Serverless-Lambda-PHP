<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

// $laravelPath = __DIR__ . '/../no-commit-old/laravel-app';

$laravelPath = $laravelPath ?? __DIR__ . '/../../laravel-app';

require_once $laravelPath . '/../utils/laravel-core.php';

if (!function_exists('printLine')) {
    /**
     * function printLine
     *
     * @param mixed $content
     *
     * @return void
     */
    function printLine($content): void
    {
        print_r($content);
        echo PHP_EOL;
    }
}

if (!function_exists('quit')) {
    /**
     * function quit
     *
     * @param string $message
     * @param int $exitStatus
     * @return void
     */
    function quit(string $message = '', int $exitStatus = 0): void
    {
        if ($message) {
            print_r($message . PHP_EOL);
        }

        exit(intval(str_replace('-', '', sprintf('%d', $exitStatus))));
    }
}

if (!function_exists('recordAttributes')) {
    /**
     * function recordAttributes
     *
     * @param mixed $record
     *
     * @return array
     */
    function recordAttributes(mixed $record): array
    {
        if (!is_array($record)) {
            return [];
        }

        return (array) ($record['messageAttributes'] ?? $record['MessageAttributes'] ?? []
        );
    }
}

if (!function_exists('recordAttribute')) {
    /**
     * function recordAttribute
     *
     * @param mixed $record
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    function recordAttribute(mixed $record, string $key, mixed $defaultValue = null): mixed
    {
        if (!is_array($record)) {
            return $defaultValue;
        }

        $messageAttributes = (array) ($record['messageAttributes'] ?? $record['MessageAttributes'] ?? []);

        $data = $messageAttributes[$key] ?? null;

        if (!$data) {
            return $defaultValue;
        }

        $binaryListValues = $data['binaryListValues'] ?? $data['BinaryListValues'] ?? null;
        $stringListValues = $data['stringListValues'] ?? $data['StringListValues'] ?? null;
        $stringValue = $data['stringValue'] ?? $data['StringValue'] ?? null;

        if ($binaryListValues) {
            return $binaryListValues;
        }

        if ($stringListValues) {
            return $stringListValues;
        }

        if ($stringValue) {
            return $stringValue;
        }

        return $defaultValue;
    }
}

if (!function_exists('jsonResponse')) {
    /**
     * jsonResponse function
     *
     * @param array|string $body
     * @param integer $status
     * @param boolean $bodyIsJson
     *
     * @return string
     */
    function jsonResponse(array|string $body, int $status = 200, bool $bodyIsJson = false): string
    {
        $bodyIsJson = is_string($body) && $bodyIsJson;

        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type',
            'Access-Control-Allow-Methods' => 'OPTIONS,POST,GET'
        ];

        // Padrão de saída
        return json_encode([
            'statusCode' => $status,
            'headers' => $headers,
            'body' => $bodyIsJson ? json_decode($body) : $body,
        ]);
    }
}
