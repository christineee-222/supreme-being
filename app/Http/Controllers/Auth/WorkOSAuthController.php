<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use WorkOS\UserManagement;
use WorkOS\WorkOS;

final class WorkOSAuthController extends Controller
{
    /**
     * Configure WorkOS SDK from config (idempotent).
     *
     * @return array{0:string,1:string} [$clientId, $redirectUrl]
     */
    private function configureWorkOS(): array
    {
        $apiKey = (string) config('services.workos.api_key');
        $clientId = (string) config('services.workos.client_id');
        $redirectUrl = (string) config('services.workos.redirect_url');

        if ($apiKey === '') {
            throw new \RuntimeException('Missing WorkOS API key (services.workos.api_key / WORKOS_API_KEY).');
        }
        if ($clientId === '') {
            throw new \RuntimeException('Missing WorkOS client id (services.workos.client_id / WORKOS_CLIENT_ID).');
        }
        if ($redirectUrl === '') {
            throw new \RuntimeException('Missing WorkOS redirect url (services.workos.redirect_url / WORKOS_REDIRECT_URL).');
        }

        WorkOS::setApiKey($apiKey);
        WorkOS::setClientId($clientId);

        return [$clientId, $redirectUrl];
    }

    /**
     * Base64URL decode helper.
     */
    private function b64urlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;

        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }

        return base64_decode($data, true) ?: '';
    }

    /**
     * Build the WorkOS User Management authorize URL.
     * IMPORTANT: provider=authkit is required for AuthKit.
     */
    private function buildUserManagementAuthorizeUrl(
        string $clientId,
        string $redirectUrl,
        string $state = ''
    ): string {
        $base = 'https://api.workos.com/user_management/authorize';

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'provider' => 'authkit',
            'scope' => 'openid email profile',
        ];

        if ($state !== '') {
            $params['state'] = $state;
        }

        return $base.'?'.http_build_query($params);
    }

    /**
     * Web + Mobile entrypoint: sends user to WorkOS authorize URL.
     * - Web: /login
     * - Mobile: /auth/workos/redirect (called by /mobile/start)
     */
    public function redirect(Request $request): RedirectResponse
    {
        [$clientId, $redirectUrl] = $this->configureWorkOS();

        $state = (string) $request->query('state', '');

        $authUrl = $this->buildUserManagementAuthorizeUrl(
            clientId: $clientId,
            redirectUrl: $redirectUrl,
            state: $state,
        );

        Log::info('WorkOS redirect invoked (user_management/authorize)', [
            'redirect_url' => $redirectUrl,
            'state_len' => strlen($state),
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * WorkOS OAuth callback: exchanges code for profile, logs user in,
     * then routes to either web dashboard or mobile complete.
     */
    public function callback(Request $request): RedirectResponse
    {
        [$clientId] = $this->configureWorkOS();

        Log::info('===== WORKOS CALLBACK START =====', [
            'state_param' => $request->query('state'),
            'code_param' => $request->query('code'),
            'full_url' => $request->fullUrl(),
        ]);

        $code = (string) $request->query('code', '');
        if ($code === '') {
            Log::warning('WorkOS callback missing code');

            return redirect()->intended(route('dashboard'));
        }

        Log::info('WorkOS authenticateWithCode input', [
            'client_id' => $clientId,
            'redirect_url' => config('services.workos.redirect_url'),
            'code_len' => strlen($code),
        ]);

        $um = new UserManagement(config('services.workos.api_key'));

        $resp = $um->authenticateWithCode(
            clientId: $clientId,
            code: $code,
            ipAddress: $request->ip(),
            userAgent: (string) $request->userAgent(),
        );

        // In this SDK version, most fields live under $resp->raw
        $data = is_array($resp->raw ?? null) ? $resp->raw : [];
        $userData = $data['user'] ?? $data['profile'] ?? $data;

        $workosId = $userData['id'] ?? null;
        $email = $userData['email'] ?? null;

        $firstName = $userData['first_name'] ?? $userData['firstName'] ?? '';
        $lastName = $userData['last_name'] ?? $userData['lastName'] ?? '';
        $name = trim($firstName.' '.$lastName);

        if (! is_string($workosId) || $workosId === '' || ! is_string($email) || $email === '') {
            Log::error('WorkOS authenticateWithCode returned unexpected shape', [
                'keys' => array_keys($data),
                'user_keys' => is_array($userData) ? array_keys($userData) : gettype($userData),
            ]);

            abort(500, 'WorkOS auth response missing user id/email.');
        }

        $user = User::firstOrCreate(
            ['workos_id' => $workosId],
            [
                'email' => $email,
                'name' => $name !== '' ? $name : $email,
            ]
        );

        Auth::login($user);

        // Detect mobile flow
        $isMobile = false;

        // Prefer session flag from /mobile/start
        if (session()->has('mobile.return_to')) {
            $isMobile = true;
        } else {
            // Fallback: decode state payload from callback
            $stateRaw = (string) $request->query('state', '');

            if ($stateRaw !== '') {
                $decoded = $this->b64urlDecode($stateRaw);

                // Back-compat: normal base64
                if ($decoded === '') {
                    $decoded = base64_decode($stateRaw, true) ?: '';
                }

                if ($decoded !== '') {
                    $stateData = json_decode($decoded, true);

                    if (is_array($stateData) && ! empty($stateData['is_mobile'])) {
                        $isMobile = true;

                        session([
                            'mobile.return_to' => $stateData['return_to'] ?? 'assemblyrequired://auth',
                            'mobile.state' => $stateData['mobile_state'] ?? '',
                        ]);
                    }
                }
            }
        }

        Log::info('WorkOS callback completed', [
            'user_id' => $user->uuid,
            'mobile_detected' => $isMobile,
            'mobile_return_to' => session('mobile.return_to'),
        ]);

        return $isMobile
            ? redirect()->route('mobile.complete')
            : redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}


