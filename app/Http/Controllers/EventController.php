<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Request $request, Event $event): \Inertia\Response
    {
        $rsvp = EventRsvp::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return Inertia::render('Events/Show', [
            'event' => $event,
            'rsvp' => $rsvp ? [
                'id' => $rsvp->id,
                'user_id' => $rsvp->user_id,
                'event_id' => $rsvp->event_id,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            'user_id' => auth()->id(),
            // real fields later
        ]);

        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update([
            // editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}
