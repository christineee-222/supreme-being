<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;
use App\Policies\Concerns\OwnsModel;

final class EventPolicy
{
    use AllowsRoles;
    use OwnsModel;

    /**
     * Anyone can view events.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Event $event): bool
    {
        return true;
    }

    /**
     * Creating events is allowed for authenticated users.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Events may be updated only before they start,
     * unless the user is an admin.
     */
    public function update(User $user, Event $event): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if (!$this->owns($user, $event)) {
            return false;
        }

        return $event->starts_at->isFuture();
    }

    /**
     * Events may be cancelled only before they start,
     * unless the user is an admin.
     */
    public function cancel(User $user, Event $event): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if (!$this->owns($user, $event)) {
            return false;
        }

        return $event->starts_at->isFuture();
    }

    /**
     * Events may never be deleted.
     */
    public function delete(User $user, Event $event): bool
    {
        return false;
    }

    /**
     * Restore is not supported.
     */
    public function restore(User $user, Event $event): bool
    {
        return false;
    }

    /**
     * Force delete is never allowed.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }
}



