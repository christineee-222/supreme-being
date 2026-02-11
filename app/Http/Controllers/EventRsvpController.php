<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Http\Requests\Api\V1\StoreEventRsvpRequest;
use App\Http\Requests\UpdateEventRsvpRequest;
use App\Http\Resources\EventRsvpResource;
use Illuminate\Http\JsonResponse;

class EventRsvpController extends Controller
{
    public function store(StoreEventRsvpRequest $request, Event $event): EventRsvpResource
    {
        $this->authorize('create', [EventRsvp::class, $event]);

        $rsvp = EventRsvp::create([
            'event_id' => $event->id,
            'user_id'  => $request->user()->id,
            'status'   => $request->validated('status'),
        ]);

        return new EventRsvpResource($rsvp);
    }

    public function update(
        UpdateEventRsvpRequest $request,
        Event $event,
        EventRsvp $rsvp
    ): EventRsvpResource {
        $this->authorize('update', $rsvp);

        $rsvp->update($request->validated());

        return new EventRsvpResource($rsvp);
    }

    public function destroy(Event $event, EventRsvp $rsvp): JsonResponse
    {
        $this->authorize('delete', $rsvp);

        $rsvp->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }
}

