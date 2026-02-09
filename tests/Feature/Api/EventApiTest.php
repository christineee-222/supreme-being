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

    public function test_events_index_requires_auth(): void
    {
        $this->getJson('/api/v1/events')
            ->assertStatus(401);
    }

    public function test_events_index_returns_events(): void
    {
        $user = User::factory()->create(['workos_id' => 'user_test_1']);
        Event::factory()->count(3)->create();

        $token = $this->actingAs($user)->postJson('/api/v1/token')->json('token');

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

        $token = $this->actingAs($user)->postJson('/api/v1/token')->json('token');

        $this->getJson("/api/v1/events/{$event->id}", ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('data.rsvp.id', $rsvp->id);
    }
}
