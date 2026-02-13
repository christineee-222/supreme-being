<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

final class MobileAuthStartController
{
    private function b64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function __invoke(Request $request): RedirectResponse
    {
        try {
            $returnTo = (string) $request->query('return_to', 'assemblyrequired://auth');
            $mobileState = (string) $request->query('state', '');

            if (! $this->isAllowedReturnTo($returnTo)) {
                $returnTo = 'assemblyrequired://auth';
            }

            $payload = json_encode([
                'return_to' => $returnTo,
                'mobile_state' => $mobileState,
                'is_mobile' => true,
            ]);

            $encodedState = $this->b64urlEncode($payload);

            session([
                'mobile.return_to' => $returnTo,
                'mobile.state' => $mobileState,
                'mobile.encoded_state' => $encodedState,
            ]);

            Log::info('Mobile auth start', [
                'return_to' => $returnTo,
                'state_len' => strlen($encodedState),
            ]);

            return redirect()->route('workos.redirect', [
                'state' => $encodedState,
            ]);

        } catch (\Throwable $e) {
            Log::error('mobile/start failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return redirect('/');
        }
    }

    private function isAllowedReturnTo(string $returnTo): bool
    {
        return parse_url($returnTo, PHP_URL_SCHEME) === 'assemblyrequired';
    }
}








