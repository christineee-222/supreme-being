<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use WorkOS\UserManagement;

class WorkOSAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.workos.api_key' => 'sk_test_fake',
            'services.workos.client_id' => 'client_test_fake',
            'services.workos.redirect_url' => 'http://localhost/auth/workos/callback',
        ]);
    }

    /**
     * Mock the WorkOS UserManagement SDK to return a fake user profile.
     */
    private function mockWorkOSAuthenticateWithCode(
        string $workosId,
        string $email,
        string $firstName = '',
        string $lastName = ''
    ): void {
        $response = new \stdClass();
        $response->raw = [
            'user' => [
                'id' => $workosId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
        ];

        $mock = Mockery::mock('overload:' . UserManagement::class);
        $mock->shouldReceive('authenticateWithCode')
            ->once()
            ->andReturn($response);
    }

    public function test_guest_visiting_login_redirects_to_workos(): void
    {
        $this->get('/login')
            ->assertRedirect()
            ->assertRedirectContains('api.workos.com/user_management/authorize');
    }

    public function test_authenticated_user_visiting_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_callback_without_code_redirects_to_login(): void
    {
        $this->get('/auth/workos/callback')
            ->assertRedirect('/login');
    }

    public function test_callback_with_code_creates_user_and_logs_in(): void
    {
        $this->mockWorkOSAuthenticateWithCode(
            workosId: 'user_workos_abc',
            email: 'jane@example.com',
            firstName: 'Jane',
            lastName: 'Doe',
        );

        $response = $this->get('/auth/workos/callback?code=test_auth_code');

        $response->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'workos_id' => 'user_workos_abc',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_callback_logs_in_existing_user_by_workos_id(): void
    {
        $existing = User::factory()->create([
            'workos_id' => 'user_workos_existing',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        $this->mockWorkOSAuthenticateWithCode(
            workosId: 'user_workos_existing',
            email: 'existing@example.com',
            firstName: 'Existing',
            lastName: 'User',
        );

        $this->get('/auth/workos/callback?code=test_auth_code')
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_session_persists_after_callback_login(): void
    {
        $this->mockWorkOSAuthenticateWithCode(
            workosId: 'user_persist_test',
            email: 'persist@example.com',
        );

        $callbackResponse = $this->get('/auth/workos/callback?code=test_auth_code');
        $callbackResponse->assertRedirect('/');

        $this->get('/dashboard')->assertOk();
    }

    public function test_session_id_changes_after_login(): void
    {
        $this->mockWorkOSAuthenticateWithCode(
            workosId: 'user_regen_test',
            email: 'regen@example.com',
        );

        $this->get('/');
        $sessionIdBefore = session()->getId();

        $this->get('/auth/workos/callback?code=test_auth_code');
        $sessionIdAfter = session()->getId();

        $this->assertNotEquals($sessionIdBefore, $sessionIdAfter);
    }

    public function test_callback_detects_mobile_flow_via_state(): void
    {
        $this->mockWorkOSAuthenticateWithCode(
            workosId: 'user_mobile_test',
            email: 'mobile@example.com',
        );

        $statePayload = base64_encode(json_encode([
            'is_mobile' => true,
            'return_to' => 'myapp://auth',
            'mobile_state' => 'xyz',
        ]));

        $this->get('/auth/workos/callback?code=test_auth_code&state=' . $statePayload)
            ->assertRedirect(route('mobile.complete'));
    }

    public function test_callback_detects_mobile_flow_via_session_flag(): void
    {
        $this->mockWorkOSAuthenticateWithCode(
            workosId: 'user_mobile_session',
            email: 'mobilesession@example.com',
        );

        $this->withSession(['mobile.return_to' => 'myapp://auth'])
            ->get('/auth/workos/callback?code=test_auth_code')
            ->assertRedirect(route('mobile.complete'));
    }

    public function test_logout_clears_session_and_redirects_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }
}


