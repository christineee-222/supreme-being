<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;

final class EventShowController extends Controller
{
    public function __invoke(Request $request, Event $event): EventResource
    {
        $user = $request->user();

        // Manual query for viewer-specific RSVP (avoids HasOne PK mismatch)
        $rsvp = $user
            ? EventRsvp::query()
                ->where('event_id', $event->binaryId())
                ->where('user_id', $user->binaryId())
                ->first()
            : null;

        // Preserve whenLoaded() compatibility in EventResource
        $event->setRelation('rsvpForViewer', $rsvp);

        // Flattened RSVP status for mobile convenience
        $event->rsvp_status = $rsvp?->status;

        return EventResource::make($event);
    }
}
