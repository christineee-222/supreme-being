<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class ModeratorDecisionPolicy
{
    use AllowsRoles;

    public function cosign(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
