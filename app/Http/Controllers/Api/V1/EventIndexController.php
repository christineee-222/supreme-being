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
        $perPage = (int) $request->query('per_page', 20);

        if ($perPage < 1) {
            $perPage = 20;
        }

        if ($perPage > 50) {
            $perPage = 50;
        }

        $events = Event::query()
            ->latest()
            ->paginate($perPage);

        return EventResource::collection($events);
    }
}
