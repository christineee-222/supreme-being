<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Uuid;

class BinaryUuid implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if (strlen($value) !== 16) {
            return $value;
        }

        return Uuid::fromBinary($value)->toRfc4122();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (strlen($value) === 16) {
            return $value;
        }

        return Uuid::fromString($value)->toBinary();
    }
}
