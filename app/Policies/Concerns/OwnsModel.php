<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait OwnsModel
{
    protected function owns(User $user, $model): bool
    {
        return isset($model->user_id) && $model->user_id === $user->id;
    }
}
