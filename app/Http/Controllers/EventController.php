<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            'user_id' => auth()->id(),
            // add real fields later
        ]);

        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update([
            // add editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}


