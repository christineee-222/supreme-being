<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

final class MobileAuthCompleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if (! $code) {
            abort(400, 'Missing authorization code');
        }

        // Look up where to send the user back in the app
        $returnTo = $state ? Cache::pull("mobile_return_to:{$state}") : null;
        $returnTo = $returnTo ?: 'assemblyrequired://auth';

        $redirect = $returnTo
            . '?code=' . urlencode($code)
            . ($state ? '&state=' . urlencode($state) : '');

        return redirect()->away($redirect);
    }
}

