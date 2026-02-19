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

        $rsvp = $user
            ? EventRsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first()
            : null;

        $event->setRelation('rsvpForViewer', $rsvp);

        $event->rsvp_status = $rsvp?->status;

        return EventResource::make($event);
    }
}
