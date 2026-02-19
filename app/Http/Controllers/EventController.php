<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\Uid\Uuid;

class EventController extends Controller
{
    public function show(Request $request, Event $event): Response
    {
        // ✅ Running tally (public)
        $rsvpsCount = EventRsvp::query()
            ->where('event_id', $event->binaryId())
            ->count();

        // ✅ Only compute user RSVP if logged in (otherwise guest-safe)
        $userRsvp = null;

        if ($request->user()) {
            $rsvp = EventRsvp::query()
                ->where('event_id', $event->binaryId())
                ->where('user_id', $request->user()->binaryId())
                ->first();

            if ($rsvp) {
                $userRsvp = [
                    'id' => $rsvp->uuid,
                    'status' => $rsvp->status,
                    'user_id' => Uuid::fromBinary($rsvp->user_id)->toRfc4122(),
                    'event_id' => $event->uuid,
                ];
            }
        }

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->uuid,
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
                'id' => $event->uuid,
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
            'user_id' => $request->user()->binaryId(),
            // title is required in DB, so either provide one here or create events via UI/tinker
            // 'title' => 'New Event',
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


