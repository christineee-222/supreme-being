<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
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

        $eventsQuery = Event::query()
            // Events should usually sort by start time, not created_at
            ->orderBy('starts_at', 'asc');

        // Only attempt viewer-specific RSVP data if authenticated.
        if ($user) {
            $eventsQuery->with('rsvpForViewer');

            // Optional: flatten RSVP status for mobile convenience.
            // This assumes your rsvpForViewer relationship returns ONE RSVP model for the viewer.
            $eventsQuery->withExists([
                // No-op placeholder if you later want exists flags
            ]);
        }

        $events = $eventsQuery->paginate($perPage);

        // Optional: flatten rsvp_status while preserving nested rsvp resource
        if ($user) {
            $events->getCollection()->transform(function ($event) {
                $event->rsvp_status = optional($event->rsvpForViewer)->status;
                return $event;
            });
        }

        return EventResource::collection($events);
    }
}


