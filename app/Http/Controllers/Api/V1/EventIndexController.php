<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventIndexController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->latest()
            ->paginate(20);

        return EventResource::collection($events);
    }
}
