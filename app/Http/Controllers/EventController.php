<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function show(Request $request, Event $event): Response
    {
        $rsvpsCount = EventRsvp::query()
            ->where('event_id', $event->id)
            ->count();

        $userRsvp = null;

        if ($request->user()) {
            $rsvp = EventRsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($rsvp) {
                $userRsvp = [
                    'id' => $rsvp->id,
                    'status' => $rsvp->status,
                    'user_id' => $rsvp->user_id,
                    'event_id' => $event->id,
                ];
            }
        }

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'status' => $event->status,
                'starts_at' => $event->starts_at,
                'rsvps_count' => $rsvpsCount,
            ],
            'userRsvp' => $userRsvp,
        ]);
    }

    public function index(): Response
    {
        $events = Event::query()
            ->latest()
            ->take(25)
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'status' => $event->status,
                'starts_at' => $event->starts_at,
            ]);

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            'user_id' => $request->user()->id,
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
