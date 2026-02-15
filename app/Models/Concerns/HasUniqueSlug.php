<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::creating(function ($model) {
            if (! empty($model->slug)) {
                return;
            }

            $source = $model->title ?? $model->name ?? '';
            $base = Str::slug($source);

            if ($base === '') {
                $base = 'item';
            }

            $slug = $base;
            $counter = 2;

            while (static::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$counter;
                $counter++;
            }

            $model->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
