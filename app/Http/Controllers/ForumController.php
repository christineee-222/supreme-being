<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Forum::class);

        $forum = Forum::create([
            'user_id' => request()->user()->id,
            // add real fields later
        ]);

        return response()->json($forum);
    }

    public function update(Request $request, Forum $forum)
    {
        $this->authorize('update', $forum);

        $forum->update([
            // add editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}
