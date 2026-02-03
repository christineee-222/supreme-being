<?php

namespace App\Policies\Traits;

use App\Models\User;

trait InteractsWithPublishableModels
{
    protected function canInteract(User $user, $model): bool
    {
        // Admins and moderators can always interact
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        // Regular users can only interact with published models
        return $model->status === 'published';
    }
}
