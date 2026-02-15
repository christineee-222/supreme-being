<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Forum;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Forum $forum)
    {
        $this->authorize('comment', $forum);

        $forum->comments()->create([
            'body' => $request->validated('body'),
            'user_id' => $request->user()->binaryId(),
        ]);

        return back();
    }
}
