<?php

namespace App\Casts;

use JsonSerializable;
use Stringable;

class DynamicPillarValue implements JsonSerializable, Stringable
{
    public function __construct(public string $value) {}

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
