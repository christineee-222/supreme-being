<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\StoreEventRsvpRequest;
use App\Http\Requests\UpdateEventRsvpRequest;
use App\Http\Resources\EventRsvpResource;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRsvpController extends Controller
{
    public function store(StoreEventRsvpRequest $request, Event $event)
    {
        $this->authorize('create', [EventRsvp::class, $event]);

        $rsvp = EventRsvp::updateOrCreate(
            [
                'event_id' => $event->binaryId(),
                'user_id' => $request->user()->binaryId(),
            ],
            [
                'status' => $request->validated('status'),
            ]
        );

        // ✅ Inertia expects a redirect / Inertia response, not plain JSON
        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return new EventRsvpResource($rsvp);
    }

    public function update(
        UpdateEventRsvpRequest $request,
        Event $event,
        EventRsvp $rsvp
    ) {
        $this->authorize('update', $rsvp);

        $rsvp->update($request->validated());

        // ✅ Same rule for Inertia
        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return new EventRsvpResource($rsvp);
    }

    public function destroy(Request $request, Event $event, EventRsvp $rsvp): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $rsvp);

        $rsvp->delete();

        // ✅ Same rule for Inertia
        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return response()->json([
            'status' => 'deleted',
        ]);
    }
}


