<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EventRsvpDestroyController extends Controller
{
    public function __invoke(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $rsvp = EventRsvp::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($rsvp) {
            // If you have an EventRsvpPolicy delete(User, EventRsvp), uncomment:
            // $this->authorize('delete', $rsvp);

            $rsvp->delete();
        }

        return response()->json([
            'meta' => [
                'deleted' => true,
            ],
        ]);
    }
}


