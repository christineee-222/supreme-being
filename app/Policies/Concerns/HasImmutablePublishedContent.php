<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

trait HasImmutablePublishedContent
{
    protected function isImmutable(object $model): bool
    {
        if (! method_exists($model, 'getAttribute')) {
            return false;
        }

        return $model->getAttribute('status') === 'published';
    }
}


