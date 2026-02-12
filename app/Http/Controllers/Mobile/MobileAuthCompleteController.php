<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MobileAuthCompleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        // Prefer return_to from the original start request if you pass it through;
        // otherwise default to your iOS scheme.
        $returnTo = $request->query('return_to', 'assemblyrequired://auth');

        if (! $code) {
            abort(400, 'Missing authorization code');
        }

        $redirect = $returnTo
            . '?code=' . urlencode($code)
            . ($state ? '&state=' . urlencode($state) : '');

        return redirect()->away($redirect);
    }
}
