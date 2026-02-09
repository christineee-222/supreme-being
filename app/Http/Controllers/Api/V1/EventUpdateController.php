<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventUpdateController extends Controller
{
    public function __invoke(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->fill($request->validated());
        $this->authorize('update', $event);
        $event->save();

        return new EventResource($event->fresh());
    }
}
