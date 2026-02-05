<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Event $event)
    {
        $user = auth()->user();

        $userRsvp = null;

        if ($user) {
            $userRsvp = EventRsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();
        }

        return Inertia::render('Events/Show', [
            'event' => $event,
            'userRsvp' => $userRsvp,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            'user_id' => auth()->id(),
            // add real fields later
        ]);

        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update([
            // add editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}



