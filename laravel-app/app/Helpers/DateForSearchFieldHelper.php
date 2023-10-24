<?php

namespace App\Helpers;

use DateTime;

class DateForSearchFieldHelper
{
    public static function get(?string $search, bool $matchDate = true)
    {
        if (!filled($search)) {
            return null;
        }

        if ($matchDate) {
            try {
                return static::matchDate($search);
            } catch (\Throwable $th) {
                return null;
            }
        }

        $search = str_replace("-", "/", $search);

        return (preg_match('/\//', $search))
            ? implode('-', array_reverse(explode('/', $search)))
            : implode('/', array_reverse(explode('-', $search)));
    }

    /**
     * matchDate function
     *
     * @param string|null $value
     * @param string|null $defaultValue  - Value when invalid date
     *  Ex: 25-05 and today are 2023-m-d ...
     *  '25-05' => 2023-05-25
     *
     * @see tests/Feature/Helpers/DateForSearchFieldHelperTest.php
     *
     * @return ?string
     */
    public static function matchDate(
        ?string $value = null,
        ?string $defaultValue = null,
    ): ?string {
        if (!filled($value)) {
            return null;
        }

        $dateFormat = [
            ['input' => 'Y', 'ouput' => 'Y',],
            ['input' => 'm', 'ouput' => 'm',],
            ['input' => 'd', 'ouput' => 'd',],
            ['input' => 'd/m', 'ouput' => 'm-d',],
            ['input' => 'm/d', 'ouput' => 'm-d',],
            ['input' => 'm-d', 'ouput' => 'm-d',],
            ['input' => 'm/Y', 'ouput' => 'Y-m',],
            ['input' => 'm-Y', 'ouput' => 'Y-m',],
            ['input' => 'Y-m-d', 'ouput' => 'Y-m-d',],
            ['input' => 'd/m/Y', 'ouput' => 'Y-m-d',],
            ['input' => 'Y-m-d H:i:s', 'ouput' => 'Y-m-d H:i:s',],
            ['input' => 'Y-m-d H:i', 'ouput' => 'Y-m-d H:i',],
            ['input' => 'Y-m-d H', 'ouput' => 'Y-m-d H',],
            ['input' => 'Y-d-m', 'ouput' => 'Y-m-d',],
            ['input' => 'm/d/Y', 'ouput' => 'Y-m-d',],
            ['input' => 'd/m/y', 'ouput' => 'Y-m-d',],
            ['input' => 'd/m/y H:i', 'ouput' => 'Y-m-d H:i',],
            ['input' => 'd/m/y H:i:s', 'ouput' => 'Y-m-d H:i:s',],
        ];

        foreach ($dateFormat as $item) {
            $inputFormat = $item['input'] ?? null;
            $ouputFormat = $item['ouput'] ?? null;

            if (!$inputFormat || !$ouputFormat) {
                continue;
            }

            if (static::dateIsValid($value, $inputFormat)) {
                return (DateTime::createFromFormat($inputFormat, $value) ?: null)?->format($ouputFormat);
            }
        }

        try {
            return (new DateTime($value))?->format('Y-m-d');
        } catch (\Throwable $th) {
            return $defaultValue;
        }
    }

    public static function dateIsValid(string|DateTime|null $date, string $format): bool
    {
        if (!$date) {
            return false;
        }

        return (DateTime::createFromFormat($format, $date) ?: null)?->format($format) === $date;
    }
}
