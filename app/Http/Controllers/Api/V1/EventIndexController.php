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

        if ($user) {
            $eventIds = $events->getCollection()->pluck('id')->all();

            $rsvps = EventRsvp::query()
                ->where('user_id', $user->id)
                ->whereIn('event_id', $eventIds)
                ->get()
                ->keyBy('event_id');

            $events->getCollection()->transform(function (Event $event) use ($rsvps) {
                $rsvp = $rsvps->get($event->id);
                $event->setRelation('rsvpForViewer', $rsvp);
                $event->rsvp_status = $rsvp?->status;

                return $event;
            });
        }

        return EventResource::collection($events);
    }
}
