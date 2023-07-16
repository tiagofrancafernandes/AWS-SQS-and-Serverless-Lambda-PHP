<?php

namespace App\IOData\DataMutators\Enums\Contracts;

interface CommonEnum
{
     /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * jsonSerialize function
     *
     * @return string
     */
    public function jsonSerialize(): string;

    /**
     * jsonSerializeAll function
     *
     * @return string
     */
    public function jsonSerializeAll(): string;
}
