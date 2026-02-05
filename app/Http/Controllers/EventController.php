<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventRsvpResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Event $event)
    {
        $event->load([
            'rsvps',
        ]);

        $rsvp = $event->rsvps
            ->firstWhere('user_id', auth()->id());

        return Inertia::render('Events/Show', [
            'event' => $event,
            'rsvp' => $rsvp
                ? EventRsvpResource::make($rsvp)
                : null,
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








