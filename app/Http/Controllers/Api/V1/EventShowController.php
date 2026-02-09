<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventShowController extends Controller
{
    public function __invoke(Request $request, Event $event): EventResource
    {
        $userId = $request->user()->id;

        $event->load([
            'rsvpForViewer' => fn ($q) => $q->where('user_id', $userId),
        ]);

        return new EventResource($event);
    }
}
