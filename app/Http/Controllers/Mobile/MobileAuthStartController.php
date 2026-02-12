<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class MobileAuthStartController
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Where to send the browser once WorkOS sends us back to /mobile/complete
        // iOS passes return_to=assemblyrequired://auth
        $returnTo = $request->query('return_to', 'assemblyrequired://auth');

        // OAuth state for CSRF protection + to look up return_to later
        $state = $request->query('state') ?: Str::uuid()->toString();

        // Persist the return target briefly (Auth code lifetime is short)
        Cache::put("mobile_return_to:{$state}", $returnTo, now()->addMinutes(10));

        // Build WorkOS authorization URL.
        // NOTE: This uses config values you should already have set.
        // You MUST ensure these exist in production:
        // - services.workos.client_id
        // - services.workos.redirect_uri (or fallback below)
        $clientId = config('services.workos.client_id');
        if (! $clientId) {
            abort(500, 'Missing WorkOS client id (services.workos.client_id).');
        }

        $redirectUri = config('services.workos.redirect_uri')
            ?: url('/mobile/complete');

        // Minimal authorize URL (works with standard OAuth authorization endpoint pattern).
        // If you use a specific WorkOS SDK method in your web controller, we can swap to that,
        // but this gets you unblocked immediately.
        $authorizeBase = rtrim((string) config('services.workos.authorize_url', 'https://api.workos.com/oauth/authorize'), '/');

        $query = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'state'         => $state,
        ]);

        $authorizeUrl = "{$authorizeBase}?{$query}";

        return redirect()->away($authorizeUrl);
    }
}

