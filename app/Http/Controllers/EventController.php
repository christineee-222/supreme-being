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
            ->where('status', 'going')
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
                'slug' => $event->slug,
                'title' => $event->title,
                'description' => $event->description,
                'status' => $event->status,
                'starts_at' => optional($event->starts_at)->toISOString(),
                'ends_at' => optional($event->ends_at)->toISOString(),
                'rsvps_count' => $rsvpsCount,
            ],
            'userRsvp' => $userRsvp,
        ]);
    }

    public function index(): Response
    {
        // Ordering goals:
        // 1) Upcoming scheduled first (soonest first)
        // 2) Past scheduled after upcoming
        // 3) Draft (no date / not ready) after scheduled
        // 4) Cancelled last
        $events = Event::query()
            ->take(25)
            ->orderByRaw("
                CASE
                    WHEN status = 'cancelled' THEN 3
                    WHEN status = 'draft' OR starts_at IS NULL THEN 2
                    WHEN starts_at < NOW() THEN 1
                    ELSE 0
                END ASC
            ")
            // Within each group: put real dated events first, then sort by starts_at
            ->orderByRaw("starts_at IS NULL ASC")
            ->orderBy('starts_at', 'asc')
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
                'status' => $event->status,
                'starts_at' => optional($event->starts_at)->toISOString(),
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
