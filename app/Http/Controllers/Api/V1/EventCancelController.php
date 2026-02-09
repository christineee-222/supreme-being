<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventCancelController extends Controller
{
    public function __invoke(Event $event): EventResource
    {
        $this->authorize('cancel', $event);

        $event->status = 'cancelled';
        $event->cancelled_at = now();
        $event->save();

        return new EventResource($event->fresh());

    }
}
