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

    public function vote(Poll $poll)
    {
        $this->authorize('vote', $poll);

    // voting logic will go here later

        return response()->json(['status' => 'vote allowed']);
    }

}



