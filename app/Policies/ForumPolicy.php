<?php

namespace App\Policies;

use App\Models\Forum;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;
use App\Policies\Concerns\OwnsModel;
use App\Policies\Traits\InteractsWithPublishableModels;
use Illuminate\Auth\Access\Response;

class ForumPolicy
{
    use AllowsRoles, OwnsModel;
    use InteractsWithPublishableModels;

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Forum $forum): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function comment(User $user, Forum $forum): bool
    {
        return $this->canInteract($user, $forum);
    }


    public function update(User $user, Forum $forum): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $forum);
    }

    public function delete(User $user, Forum $forum): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $forum);
    }

    public function restore(User $user, Forum $forum): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Forum $forum): bool
    {
        return $this->isAdmin($user);
    }
}

