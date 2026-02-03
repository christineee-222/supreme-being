<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function update(Request $request, Donation $donation)
    {
        $this->authorize('update', $donation);

        return response()->json(['status' => 'authorized']);
    }
}

