<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EventApiTest extends TestCase
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

    private function mintToken(User $user): string
    {
        return (string) $this->actingAs($user)->postJson('/api/v1/token')->json('token');
    }

    public function test_events_index_requires_auth(): void
    {
        $this->getJson('/api/v1/events')
            ->assertStatus(401);
    }

    public function test_events_index_returns_events(): void
    {
        $user = User::factory()->create(['workos_id' => 'user_test_1']);
        Event::factory()->count(3)->create();

        $token = $this->mintToken($user);

        $this->getJson('/api/v1/events', ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title'],
                ],
            ]);
    }

    public function test_event_show_includes_rsvp_when_exists(): void
    {
        $user = User::factory()->create(['workos_id' => 'user_test_2']);
        $event = Event::factory()->create();

        $rsvp = EventRsvp::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $token = $this->mintToken($user);

        $this->getJson("/api/v1/events/{$event->id}", ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('data.rsvp.id', $rsvp->id);
    }

    public function test_event_create_requires_auth(): void
    {
        $this->postJson('/api/v1/events', [
            'title' => 'Town Hall',
        ])->assertStatus(401);
    }

    public function test_authenticated_user_can_create_event(): void
    {
        $user = User::factory()->create(['workos_id' => 'user_test_create']);
        $token = $this->mintToken($user);

        $response = $this->postJson('/api/v1/events', [
            'title' => 'Town Hall',
            'description' => 'Community Q&A',
            'starts_at' => now()->addDay()->toISOString(),
            'ends_at' => now()->addDays(2)->toISOString(),
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful()
            ->assertJsonPath('data.title', 'Town Hall');
    }

    public function test_non_owner_cannot_update_event(): void
    {
        $owner = User::factory()->create(['workos_id' => 'user_owner']);
        $other = User::factory()->create(['workos_id' => 'user_other']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'starts_at' => now()->addDay(),
        ]);

        $token = $this->mintToken($other);

        $this->patchJson("/api/v1/events/{$event->id}", [
            'title' => 'Edited Title',
        ], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(403);
    }

    public function test_owner_can_update_event_before_it_starts(): void
    {
        $owner = User::factory()->create(['workos_id' => 'user_owner_future']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'starts_at' => now()->addDay(),
        ]);

        $token = $this->mintToken($owner);

        $this->patchJson("/api/v1/events/{$event->id}", [
            'title' => 'Updated Title',
        ], ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_owner_cannot_update_event_after_it_starts(): void
    {
        $owner = User::factory()->create(['workos_id' => 'user_owner_past']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'starts_at' => now()->subMinute(),
        ]);

        $token = $this->mintToken($owner);

        $this->patchJson("/api/v1/events/{$event->id}", [
            'title' => 'Should Not Update',
        ], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(403);
    }

    public function test_non_owner_cannot_cancel_event(): void
    {
        $owner = User::factory()->create(['workos_id' => 'user_cancel_owner']);
        $other = User::factory()->create(['workos_id' => 'user_cancel_other']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'starts_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $token = $this->mintToken($other);

        $this->postJson("/api/v1/events/{$event->id}/cancel", [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(403);
    }

    public function test_owner_can_cancel_event_before_it_starts(): void
    {
        $owner = User::factory()->create(['workos_id' => 'user_cancel_owner_future']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'starts_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $token = $this->mintToken($owner);

        $this->postJson("/api/v1/events/{$event->id}/cancel", [], ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonStructure([
                'data' => ['cancelled_at'],
            ]);

        $this->assertNotNull($event->fresh()->cancelled_at);
    }

    public function test_owner_cannot_cancel_event_after_it_starts(): void
    {
        $owner = User::factory()->create(['workos_id' => 'user_cancel_owner_past']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'starts_at' => now()->subMinute(),
            'status' => 'scheduled',
        ]);

        $token = $this->mintToken($owner);

        $this->postJson("/api/v1/events/{$event->id}/cancel", [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(403);
    }
}
