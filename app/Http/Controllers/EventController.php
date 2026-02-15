<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\Uid\Uuid;

class EventController extends Controller
{
    public function show(Request $request, Event $event): \Inertia\Response
    {
        $rsvp = EventRsvp::query()
            ->where('event_id', $event->binaryId())
            ->where('user_id', $request->user()->binaryId())
            ->first();

        return Inertia::render('Events/Show', [
            'event' => $event,
            'rsvp' => $rsvp ? [
                'id' => $rsvp->uuid,
                'user_id' => Uuid::fromBinary($rsvp->user_id)->toRfc4122(),
                'event_id' => $event->uuid,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            'user_id' => request()->user()->binaryId(),
        ]);

        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update([]);

        return response()->json(['status' => 'authorized']);
    }
}
