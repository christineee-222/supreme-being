<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Donation::class);

        $donation = Donation::create([
            'user_id' => auth()->id(),
            // add real fields later
        ]);

        return response()->json($donation);
    }

    public function update(Request $request, Donation $donation)
    {
        $this->authorize('update', $donation);

        $donation->update([
            // add editable fields later
        ]);

        return response()->json(['status' => 'authorized']);
    }
}


