<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class ViolationPolicy
{
    use AllowsRoles;

    public function viewAny(User $user): bool
    {
        return $this->isAdminOrModerator($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrModerator($user);
    }
}
