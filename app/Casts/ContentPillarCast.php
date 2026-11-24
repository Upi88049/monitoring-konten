<?php

namespace App\Casts;

use App\Enums\ContentPillar;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ContentPillarCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $enum = ContentPillar::tryFrom($value);
        if ($enum !== null) {
            return $enum;
        }

        return new DynamicPillarValue($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ContentPillar) {
            return $value->value;
        }

        if (is_object($value) && property_exists($value, 'value')) {
            return $value->value;
        }

        return (string) $value;
    }
}
