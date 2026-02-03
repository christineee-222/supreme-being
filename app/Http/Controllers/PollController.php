<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function update(Request $request, Poll $poll)
    {
        $this->authorize('update', $poll);

        return response()->json(['status' => 'authorized']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Poll::class);

        $poll = Poll::create([
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => 'draft',
        ]);

        return response()->json($poll, 201);
    }
}



