<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::ensureDirectoryExists(storage_path('oauth'));

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);

        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'];

        File::put(storage_path('oauth/workos-private.key'), $privateKey);
        File::put(storage_path('oauth/workos-public.key'), $publicKey);
    }

    public function test_me_requires_a_bearer_token(): void
    {
        $this->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJson([
                'error' => 'MISSING_TOKEN',
            ]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->getJson('/api/v1/me', [
            'Authorization' => 'Bearer not-a-real-jwt',
        ])
            ->assertStatus(401)
            ->assertJson([
                'error' => 'INVALID_TOKEN',
            ]);
    }

    public function test_can_mint_token_via_web_session_and_use_it_on_me(): void
    {
        $user = User::factory()->create([
            'workos_id' => 'user_test_123',
        ]);

        $tokenResponse = $this->actingAs($user)
            ->postJson('/api/v1/token');

        $tokenResponse->assertOk()->assertJsonStructure(['token']);

        $token = $tokenResponse->json('token');

        $this->getJson('/api/v1/me', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'email', 'workos_id'],
            ])
            ->assertJsonPath('data.workos_id', 'user_test_123');
    }
}
