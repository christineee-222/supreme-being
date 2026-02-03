<?php

namespace App\Http\Controllers;

use App\Models\Portrait;
use Illuminate\Http\Request;

class PortraitController extends Controller
{
    public function update(Request $request, Portrait $portrait)
    {
        $this->authorize('update', $portrait);

        return response()->json(['status' => 'authorized']);
    }
}
