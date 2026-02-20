<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;

class ReportPolicy
{
    use AllowsRoles;

    public function viewAny(User $user): bool
    {
        return $this->isAdminOrModerator($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function assign(User $user, Report $report): bool
    {
        if (! $this->isAdminOrModerator($user)) {
            return false;
        }

        return $report->reported_user_id !== $user->id;
    }

    public function resolve(User $user, Report $report): bool
    {
        if (! $this->isAdminOrModerator($user)) {
            return false;
        }

        return $report->reporter_id !== $user->id && $report->reported_user_id !== $user->id;
    }

    public function escalate(User $user, Report $report): bool
    {
        return $user->isModerator();
    }
}
