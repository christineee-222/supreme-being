<?php

namespace App\Models\Concerns;

use App\Casts\BinaryUuidFk;
use Symfony\Component\Uid\Uuid;

trait UsesBinaryUuidV7
{

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }


    protected static function bootUsesBinaryUuidV7(): void
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::v7()->toBinary();
            }
        });
    }

    /**
     * UUID string accessor — the canonical external identifier.
     * Use this in URLs, API responses, JWT claims, and tests.
     */
    public function getUuidAttribute(): string
    {
        $raw = $this->getRawOriginal($this->getKeyName());

        if (is_resource($raw)) {
            $raw = stream_get_contents($raw);
        }

        return Uuid::fromBinary($raw)->toRfc4122();
    }

    /**
     * Get the raw binary value of this model's primary key.
     * Use this for WHERE clauses and FK comparisons.
     */
    public function binaryId(): string
    {
        return $this->getRawOriginal($this->getKeyName());
    }

    /**
     * Resolve route model binding, converting UUID strings to binary for PK lookups.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        $field = $field ?? $this->getRouteKeyName();

        if ($field === $this->getKeyName()) {
            try {
                $value = Uuid::fromString($value)->toBinary();
            } catch (\InvalidArgumentException) {
                return null;
            }
        }

        return $this->where($field, $value)->first();
    }

    /**
     * Fail-safe: convert binary PK and FK attributes to UUID strings during serialization.
     * Prevents malformed UTF-8 crashes in Inertia, dd(), or accidental raw model output.
     * Resources remain the authoritative formatting layer.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        // Convert PK if it's raw 16-byte binary
        $pk = $this->getKeyName();
        if (isset($attributes[$pk]) && is_string($attributes[$pk]) && strlen($attributes[$pk]) === 16) {
            $attributes[$pk] = Uuid::fromBinary($attributes[$pk])->toRfc4122();
        }

        // Convert BinaryUuidFk attributes
        foreach ($this->getCasts() as $key => $castType) {
            if ($castType === BinaryUuidFk::class && isset($attributes[$key]) && is_string($attributes[$key]) && strlen($attributes[$key]) === 16) {
                $attributes[$key] = Uuid::fromBinary($attributes[$key])->toRfc4122();
            }
        }

        // Convenience field
        $attributes['uuid'] = $this->uuid;

        return $attributes;
    }
}
