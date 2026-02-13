<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class MobileAuthCompleteController
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        try {
            Log::info('MobileAuthComplete: start', [
                'authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'session_id' => $request->session()->getId(),
            ]);

            // Ensure user is authenticated first
            if (! auth()->check()) {
                Log::warning('MobileAuthComplete: not authenticated → login');

                $request->session()->put(
                    'url.intended',
                    route('mobile.complete')
                );

                return redirect()->route('login');
            }

            $returnTo = (string) $request->session()->get(
                'mobile.return_to',
                'assemblyrequired://auth'
            );

            $state = (string) $request->session()->get('mobile.state', '');

            Log::info('MobileAuthComplete: session data', [
                'return_to' => $returnTo,
                'state_len' => strlen($state),
            ]);

            // Extra safety: allow only your custom scheme
            if (! $this->isAllowedReturnTo($returnTo)) {
                Log::warning('Invalid return_to scheme, forcing default');
                $returnTo = 'assemblyrequired://auth';
            }

            // Generate short-lived single-use auth code
            $code = Str::random(48);

            Cache::put(
                "mobile_auth_code:{$code}",
                auth()->id(),
                now()->addMinutes(5) // slightly longer to avoid race issues
            );

            Log::info('MobileAuthComplete: auth code generated', [
                'code_length' => strlen($code),
                'user_id' => auth()->id(),
            ]);

            // Build deep link
            $redirectUrl = $this->appendQueryParams(
                $returnTo,
                array_filter([
                    'code' => $code,
                    'state' => $state,
                ], fn ($v) => $v !== '')
            );

            Log::info('MobileAuthComplete: redirecting to app', [
                'url' => $redirectUrl,
            ]);

            // IMPORTANT:
            // HTML redirect works better than HTTP redirect for ASWebAuthenticationSession
            return response($this->getRedirectHtml($redirectUrl), 200)
                ->header('Content-Type', 'text/html');

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

        // Important for custom scheme URLs
        $base = $scheme !== '' ? "{$scheme}://" : '';
        $base .= $host !== '' ? "{$host}{$port}" : '';
        $base .= $path;

        return $query ? "{$base}?{$query}{$fragment}" : "{$base}{$fragment}";
    }

    private function getRedirectHtml(string $url): string
    {
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting…</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<p>Signing you in…</p>

<script>
    // Works best for iOS auth session
    window.location.replace('{$escapedUrl}');
</script>

<noscript>
    <a href="{$escapedUrl}">Continue</a>
</noscript>

</body>
</html>
HTML;
    }
}



