<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class MobileAuthCompleteController
{
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            // If not logged in yet, go to /login, then come back here
            if (! auth()->check()) {
                $request->session()->put('url.intended', route('mobile.complete'));
                return redirect()->route('login');
            }

            $returnTo = (string) ($request->session()->get('mobile.return_to') ?? 'assemblyrequired://auth');
            $state = (string) ($request->session()->get('mobile.state') ?? '');

            // Extra safety: only allow your app scheme
            if (! $this->isAllowedReturnTo($returnTo)) {
                $returnTo = 'assemblyrequired://auth';
            }

            // Create a short-lived, single-use auth code
            $code = Str::random(48);

            Cache::put(
                "mobile_auth_code:{$code}",
                auth()->id(),
                now()->addMinutes(2)
            );

            // Build: assemblyrequired://auth?code=...&state=...
            $redirectUrl = $this->appendQueryParams($returnTo, array_filter([
                'code' => $code,
                'state' => $state,
            ], fn ($v) => $v !== ''));

            return redirect()->away($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('mobile/complete failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return redirect('/');
        }
    }

    private function isAllowedReturnTo(string $returnTo): bool
    {
        $scheme = parse_url($returnTo, PHP_URL_SCHEME);
        return $scheme === 'assemblyrequired';
    }

    private function appendQueryParams(string $url, array $params): string
    {
        $parts = parse_url($url);

        $existing = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $existing);
        }

        $merged = array_merge($existing, $params);
        $query = http_build_query($merged);

        $scheme   = $parts['scheme'] ?? '';
        $host     = $parts['host'] ?? '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path     = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        // NOTE: for custom schemes like assemblyrequired://auth
        // parse_url treats "auth" as host. This rebuild keeps that.
        $base = $scheme !== '' ? "{$scheme}://" : '';
        $base .= $host !== '' ? "{$host}{$port}" : '';
        $base .= $path;

        return $query ? "{$base}?{$query}{$fragment}" : "{$base}{$fragment}";
    }
}


