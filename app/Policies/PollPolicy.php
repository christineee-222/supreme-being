<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;
use App\Policies\Concerns\OwnsModel;
use App\Policies\Traits\InteractsWithPublishableModels;
use Illuminate\Auth\Access\Response;

class PollPolicy
{
    use AllowsRoles, OwnsModel;
    use InteractsWithPublishableModels;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Poll $poll): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Poll $poll): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $poll);
    }

    public function delete(User $user, Poll $poll): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->owns($user, $poll);
    }

    public function comment(User $user, Poll $poll): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $poll->status === 'published';
    }

    public function vote(User $user, Poll $poll): bool
    {
        return $this->canInteract($user, $poll);
    }


    public function restore(User $user, Poll $poll): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Poll $poll): bool
    {
        return $this->isAdmin($user);
    }
}




