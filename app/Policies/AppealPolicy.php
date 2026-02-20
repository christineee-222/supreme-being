<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class AppealPolicy
{
    use AllowsRoles;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $user->is_indefinitely_restricted;
    }

    public function decide(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
