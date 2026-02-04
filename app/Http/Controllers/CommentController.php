<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Forum $forum)
    {
        $this->authorize('comment', $forum);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $forum->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        return back();
    }
}

