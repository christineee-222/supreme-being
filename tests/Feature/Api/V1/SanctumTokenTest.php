<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SanctumTokenTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function mobile_token_requires_code(): void
    {
        $this->postJson('/api/v1/mobile/token', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function mobile_token_rejects_empty_code(): void
    {
        $this->postJson('/api/v1/mobile/token', ['code' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function authenticated_user_can_access_me_with_sanctum_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->uuid);
    }

    #[Test]
    public function unauthenticated_request_to_me_returns_401(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    #[Test]
    public function logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('civic-mobile');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ])->postJson('/api/v1/logout')
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    #[Test]
    public function logout_without_auth_returns_401(): void
    {
        $this->postJson('/api/v1/logout')
            ->assertUnauthorized();
    }

    #[Test]
    public function sanctum_protects_events_index(): void
    {
        $this->getJson('/api/v1/events')
            ->assertUnauthorized();
    }

    #[Test]
    public function authenticated_user_can_access_events(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/events')
            ->assertOk();
    }
}
