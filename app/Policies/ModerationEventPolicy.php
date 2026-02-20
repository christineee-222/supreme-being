<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class ModerationEventPolicy
{
    use AllowsRoles;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
