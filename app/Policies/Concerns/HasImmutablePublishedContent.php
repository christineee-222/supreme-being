<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

trait HasImmutablePublishedContent
{
    protected function cannotModifyIfPublished(User $user, object $model): bool
    {
        if (! property_exists($model, 'status')) {
            return false;
        }

        return $model->status === 'published';
    }
}
