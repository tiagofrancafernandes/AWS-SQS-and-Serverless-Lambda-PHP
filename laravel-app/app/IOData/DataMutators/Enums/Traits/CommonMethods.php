<?php

namespace App\IOData\DataMutators\Enums\Traits;

use App\Helpers\Array\ArrayHelpers;

trait CommonMethods
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function all(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function flipAll(): array
    {
        return array_flip(self::all());
    }

    /**
     * get function
     *
     * @param string|integer $value
     * @param integer $searchBy  1='value' | 2='name'
     * @return void
     */
    public static function get(string|int $value, int $searchBy = 1) //: static|null
    {
        if (!$value) {
            return null;
        }

        $key = $searchBy == 2 ? 'name' : 'value';

        return ArrayHelpers::arrayFirstWhen(self::cases(), fn ($enum) => ($enum?->{$key} ?? null) == $value);
    }

    public function jsonSerialize(): string
    {
        return json_encode(
            array_filter([
                'name' => $this?->name ?? null,
                'value' => $this?->value ?? null,
            ]),
            64
        );
    }

    public function jsonSerializeAll(): string
    {
        return json_encode($this->all(), 64);
    }

    /**
     * Get the instance as an array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    public function toJson(int $flags = 64, int $depth = 512): string
    {
        return json_encode($this->all(), $flags, $depth) ?: '{}';
    }
}
