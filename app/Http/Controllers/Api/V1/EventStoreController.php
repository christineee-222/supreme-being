<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventStoreController extends Controller
{
    public function __invoke(StoreEventRequest $request): EventResource
    {
        $this->authorize('create', Event::class);

        $event = Event::query()->create([
            ...$request->validated(),
            'user_id' => $request->user()->binaryId(),
        ]);

        return new EventResource($event);
    }
}
