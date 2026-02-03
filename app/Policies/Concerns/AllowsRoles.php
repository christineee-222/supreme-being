<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AllowsRoles
{
    protected function isAdminOrModerator(User $user): bool
    {
        return $user->isAdmin() || $user->isModerator();
    }

    protected function isAdmin(User $user): bool
    {
        return $user->isAdmin();
    }
}
