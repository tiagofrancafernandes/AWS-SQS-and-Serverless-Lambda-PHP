<?php

namespace App\Helpers\Array;

class ArrayHelpers
{
    /**
     * function implodeAssoc
     *
     * @param array $array
     * @return string
     */
    public static function implodeAssoc(
        array $array,
        string $itemsGlue = ' ',
        string $template = '##_KEY_##="##_VALUE_##"',
        string $endOfString = ''
    ): string {
        if (!static::isAssoc($array)) {
            return '';
        }

        foreach ($array as $key => $value) {
            $result[] = \str_replace(
                [
                    '##_KEY_##',
                    '##_VALUE_##'
                ],
                [
                    $key,
                    $value,
                ],
                $template
            );
        }

        if (!count($result ?? [])) {
            return '';
        }

        return \implode(
            $itemsGlue,
            $result
        ) . $endOfString;
    }

    /**
     * isAssoc function
     *
     * @param array $array
     * @return boolean
     */
    public static function isAssoc(array $array)
    {
        if ([] === $array) {
            return false;
        }

        return count(
            \array_filter(
                array_keys($array),
                'is_numeric'
            )
        ) === 0;
    }

    /**
     * isList function
     *
     * @param array $array
     * @return boolean
     */
    public static function isList(array $array)
    {
        if ([] === $array) {
            return false;
        }

        $arrayKeys = array_keys($array);

        return count(
            \array_filter(
                $arrayKeys,
                'is_numeric'
            )
        ) === count(
            $arrayKeys
        );
    }

    /**
     * flatten function
     *
     * @param array $multiDimArray
     * @return array
     */
    public static function flatten(array $multiDimArray): array
    {
        $localFlatten = [];

        foreach ($multiDimArray as $key => $value) {
            if (\is_array($value)) {
                foreach (static::flatten($value) as $subKey => $subValue) {
                    $localFlatten[$subKey] = $subValue;
                }
                continue;
            }

            $localFlatten[$key] = $value;
        }

        return $localFlatten;
    }

    /**
     * arrayFirstWhen function
     *
     * @param array $array
     * @param callable $filter
     * @return mixed
     */
    public static function arrayFirstWhen(array $array, callable $filter): mixed
    {
        $first = null;

        array_filter($array, function ($item, $key) use (&$first, $filter) {
            if ($first || !$filter($item, $key)) {
                return;
            }

            $first = $item;
        }, ARRAY_FILTER_USE_BOTH);

        return $first ?? null;
    }

    /**
     * get function
     *
     * @param array $data
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function get(array $data, string $key, mixed $defaultValue = null): mixed
    {
        return $data[$key] ?? $defaultValue ?? null;
    }

    /**
     * stringToArray function
     *
     * @param string|null $data
     * @param string $decoder  `'unserialize', 'json_decode', 'unserialize|json_decode', 'json_decode|unserialize'`
     * @param boolean $throw
     *
     * @return array
     */
    public static function stringToArray(
        ?string $data,
        string $decoder = 'json_decode|unserialize',
        bool $throw = false
    ): array {
        try {
            $data = trim((string) $data);

            $allowedDecoders = [
                'unserialize',
                'json_decode',
                'unserialize|json_decode',
                'json_decode|unserialize',
            ];

            if (!$decoder || !in_array($decoder, $allowedDecoders, true)) {
                if ($throw) {
                    throw new \Exception(
                        spf(
                            'Invalid "decoder" param. Valid values: [%s]',
                            implode(',', $allowedDecoders),
                        ),
                        1
                    );
                }

                return [];
            }

            if (!$data) {
                return [];
            }

            return (array) match ($decoder) {
                'unserialize' => static::safeUnserialize($data, []),
                'json_decode' => json_decode($data, true),
                'unserialize|json_decode' => static::safeUnserialize($data, []) ?: json_decode($data, true),
                'json_decode|unserialize' => json_decode($data, true) ?: static::safeUnserialize($data, []),
                default => (array) $data,
            };
        } catch (\Throwable $th) {
            if ($throw) {
                throw $th;
            }

            \Log::error($th);

            return [];
        }
    }

    /**
     * serialized function
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public static function serialized(mixed $value): bool
    {
        try {
            if (!$value || !is_string($value) || trim(strlen($value)) < 2 || !str_contains($value, ':')) {
                return false;
            }

            unserialize($value, [
                'max_depth' => 0,
            ]);

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * safeUnserialize function
     *
     * @param mixed $value
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public static function safeUnserialize(mixed $value, mixed $defaultValue = null): mixed
    {
        try {
            if (!static::serialized($value)) {
                return $defaultValue;
            }

            return unserialize($value, [
                'max_depth' => 0,
            ]);
        } catch (\Throwable $th) {
            return $defaultValue;
        }
    }
}
