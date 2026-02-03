<?php

namespace App\Http\Controllers;

use App\Models\Legislation;
use Illuminate\Http\Request;

class LegislationController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Legislation::class);

        $legislation = Legislation::create([
            'user_id' => auth()->id(),
            // add real fields later
        ]);

        return response()->json($legislation);
    }

    public function update(Request $request, Legislation $legislation)
    {
        $this->authorize('update', $legislation);

        $legislation->update([
            // add editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}

