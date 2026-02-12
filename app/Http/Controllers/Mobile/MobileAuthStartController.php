<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class MobileAuthStartController
{
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            // iOS passes this (ex: assemblyrequired://auth)
            $returnTo = (string) $request->query('return_to', '');
            $state = (string) $request->query('state', '');

            // Only allow your app scheme to prevent open-redirect abuse.
            // If you want to allow HTTPS for debugging, you can add 'https' here.
            if (! $this->isAllowedReturnTo($returnTo)) {
                // Safe default: your app scheme endpoint.
                $returnTo = 'assemblyrequired://auth';
            }

            // Remember these for /mobile/complete
            $request->session()->put('mobile.return_to', $returnTo);
            $request->session()->put('mobile.state', $state);

            // After AuthKit finishes, your callback should redirect()->intended(),
            // so make intended be /mobile/complete.
            $request->session()->put('url.intended', route('mobile.complete'));

            // Kick off AuthKit via your existing /login route.
            return redirect()->route('login');
        } catch (\Throwable $e) {
            Log::error('mobile/start failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            // Fail closed: send user somewhere safe (home)
            return redirect('/');
        }
    }

    private function isAllowedReturnTo(string $returnTo): bool
    {
        if ($returnTo === '') {
            return false;
        }

        $scheme = parse_url($returnTo, PHP_URL_SCHEME);

        return $scheme === 'assemblyrequired';
    }
}







