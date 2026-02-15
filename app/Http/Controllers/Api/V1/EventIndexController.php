<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class EventIndexController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 20);

        if ($perPage < 1) {
            $perPage = 20;
        }

        if ($perPage > 50) {
            $perPage = 50;
        }

        $user = $request->user();

        $events = Event::query()
            ->orderBy('starts_at', 'asc')
            ->paginate($perPage);

        // Manually attach viewer-specific RSVP data (avoids HasOne PK mismatch)
        if ($user) {
            $eventIds = $events->getCollection()->map->binaryId()->all();

            $rsvps = EventRsvp::query()
                ->where('user_id', $user->binaryId())
                ->whereIn('event_id', $eventIds)
                ->get()
                ->keyBy(fn (EventRsvp $r) => $r->event_id);

            $events->getCollection()->transform(function (Event $event) use ($rsvps) {
                $rsvp = $rsvps->get($event->binaryId());
                $event->setRelation('rsvpForViewer', $rsvp);
                $event->rsvp_status = $rsvp?->status;

                return $event;
            });
        }

        return EventResource::collection($events);
    }
}
