<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEventRsvpRequest;
use App\Http\Resources\EventRsvpResource;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EventRsvpController extends Controller
{
    public function store(Request $request, Event $event)
    {
        // Ensure user is authenticated (prefer route middleware, but this prevents null user surprises).
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        // Authorize against the "set RSVP" rules.
        $this->authorize('create', [EventRsvp::class, $event]);

        $validated = $request->validate([
            'status' => ['required', 'in:going,interested,not_going'],
        ]);

        $rsvp = EventRsvp::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'status' => $validated['status'],
            ]
        );

        // Inertia form submit
        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        // API fallback
        return new EventRsvpResource($rsvp);
    }

    public function update(
        UpdateEventRsvpRequest $request,
        Event $event,
        EventRsvp $rsvp
    ) {
        $this->authorize('update', $rsvp);

        $rsvp->update($request->validated());

        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return new EventRsvpResource($rsvp);
    }

    public function destroy(
        Request $request,
        Event $event,
        EventRsvp $rsvp
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $rsvp);

        $rsvp->delete();

        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return response()->json([
            'status' => 'deleted',
        ]);
    }
}