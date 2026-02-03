<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        return response()->json(['status' => 'authorized']);
    }
}

