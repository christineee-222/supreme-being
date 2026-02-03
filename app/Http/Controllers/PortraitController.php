<?php

namespace App\Http\Controllers;

use App\Models\Portrait;
use Illuminate\Http\Request;

class PortraitController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Portrait::class);

        $portrait = Portrait::create([
            'user_id' => auth()->id(),
            // add real fields later
        ]);

        return response()->json($portrait);
    }

    public function update(Request $request, Portrait $portrait)
    {
        $this->authorize('update', $portrait);

        $portrait->update([
            // add editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}

