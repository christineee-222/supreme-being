<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;

class EventRsvpPolicy
{
    /**
     * This authorization is used by the "set my RSVP" endpoint which uses updateOrCreate().
     * So we allow authenticated users to call it as long as the event can still be RSVPed to.
     */
    public function create(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($event->hasStarted()) {
            return false;
        }

        if ($event->isCancelled() || $event->status === 'cancelled') {
            return false;
        }

        // IMPORTANT: Do NOT block when an RSVP already exists.
        // The controller uses updateOrCreate, so "create" here effectively means "set RSVP".
        return true;
    }

    public function update(User $user, EventRsvp $rsvp): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $rsvp->user_id === $user->id
            && ! $rsvp->event->hasStarted()
            && $rsvp->event->status !== 'cancelled';
    }

    public function delete(User $user, EventRsvp $rsvp): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $rsvp->user_id === $user->id
            && ! $rsvp->event->hasStarted()
            && $rsvp->event->status !== 'cancelled';
    }
}
