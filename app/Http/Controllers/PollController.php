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
}


