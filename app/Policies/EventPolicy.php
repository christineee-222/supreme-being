<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;
use App\Policies\Concerns\OwnsModel;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    use AllowsRoles, OwnsModel;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Event $event): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $event);
    }

    public function restore(User $user, Event $event): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Event $event): bool
    {
        return $this->isAdmin($user);
    }
}

