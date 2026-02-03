<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function update(Request $request, Forum $forum)
    {
        $this->authorize('update', $forum);

        return response()->json(['status' => 'authorized']);
    }
}

