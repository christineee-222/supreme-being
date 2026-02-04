<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRsvp;

class EventRsvpPolicy
{
    /**
     * Create an RSVP
     */
    public function create(User $user, Event $event): bool
    {
        // Admin override
        if ($user->isAdmin()) {
            return true;
        }

        // Event must be upcoming
        if ($event->hasStarted()) {
            return false;
        }

        // Event must not be cancelled
        if ($event->status === 'cancelled') {
            return false;
        }

        // User cannot RSVP twice
        return ! EventRsvp::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->exists();
    }

    /**
     * Update RSVP
     */
    public function update(User $user, EventRsvp $rsvp): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return
            $rsvp->user_id === $user->id &&
            ! $rsvp->event->hasStarted();
    }

    /**
     * Delete RSVP
     */
    public function delete(User $user, EventRsvp $rsvp): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return
            $rsvp->user_id === $user->id &&
            ! $rsvp->event->hasStarted();
    }
}

