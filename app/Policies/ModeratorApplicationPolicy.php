<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class ModeratorApplicationPolicy
{
    use AllowsRoles;

    public function create(User $user): bool
    {
        if ($user->violation_count > 0) {
            return false;
        }

        if ($user->is_indefinitely_restricted || ($user->restriction_ends_at !== null && $user->restriction_ends_at->isFuture())) {
            return false;
        }

        if ($user->account_created_at === null || $user->account_created_at->diffInDays(now()) < 90) {
            return false;
        }

        $hasPendingOrDeferred = $user->moderatorApplication()
            ->whereIn('status', ['pending', 'deferred'])
            ->exists();

        if ($hasPendingOrDeferred) {
            return false;
        }

        return true;
    }

    public function decide(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
