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
        $event->load('rsvpForViewer');

        return EventResource::make($event);
    }
}


