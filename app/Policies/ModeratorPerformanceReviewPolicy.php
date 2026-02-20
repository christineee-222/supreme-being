<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class ModeratorPerformanceReviewPolicy
{
    use AllowsRoles;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function decide(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
