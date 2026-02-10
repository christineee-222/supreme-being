<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

final class EventShowController extends Controller
{
    public function __invoke(Request $request, Event $event): EventResource
    {
        // Viewer-specific RSVP (relationship should be scoped to the authenticated viewer)
        $event->load('rsvpForViewer');

        // Optional: flattened RSVP status for mobile convenience
        $event->rsvp_status = optional($event->rsvpForViewer)->status;

        return EventResource::make($event);
    }
}



