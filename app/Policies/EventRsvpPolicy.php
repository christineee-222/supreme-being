<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;

class EventRsvpPolicy
{
    /**
     * Create an RSVP
     */
    public function create(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($event->hasStarted()) {
            return false;
        }

        if ($event->status === 'cancelled') {
            return false;
        }

        return ! EventRsvp::where('user_id', $user->binaryId())
            ->where('event_id', $event->binaryId())
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
            $rsvp->user_id === $user->binaryId() &&
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
            $rsvp->user_id === $user->binaryId() &&
            ! $rsvp->event->hasStarted();
    }
}
