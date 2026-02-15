<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEventRsvpRequest;
use App\Http\Resources\EventRsvpResource;
use App\Models\Event;
use App\Models\EventRsvp;

final class EventRsvpStoreController extends Controller
{
    public function __invoke(StoreEventRsvpRequest $request, Event $event): EventRsvpResource
    {
        $user = $request->user();

        // Optional policy gate (recommended once you have it)
        // $this->authorize('create', [EventRsvp::class, $event]);

        $status = $request->string('status')->toString();

        $rsvp = EventRsvp::query()->updateOrCreate(
            [
                'user_id' => $user->binaryId(),
                'event_id' => $event->binaryId(),
            ],
            [
                'status' => $status,
            ]
        );

        return new EventRsvpResource($rsvp);
    }
}
