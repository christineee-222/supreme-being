<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventsAndRsvpTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_returns_paginated_events(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Event::factory()->count(3)->create([
            'starts_at' => now()->addDay(),
        ]);

        $this->getJson('/api/v1/events?per_page=2')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    #[Test]
    public function show_returns_single_event(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $event = Event::factory()->create([
            'starts_at' => now()->addDay(),
        ]);

        $this->getJson("/api/v1/events/{$event->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'starts_at'],
            ])
            ->assertJsonPath('data.id', $event->id);
    }

    #[Test]
    public function put_rsvp_is_idempotent_and_returns_resource(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/events/{$event->id}/rsvp", [
            'status' => 'going',
        ])->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'status', 'user_id', 'event_id', 'created_at', 'updated_at']])
            ->assertJsonPath('data.status', 'going');

        $this->putJson("/api/v1/events/{$event->id}/rsvp", [
            'status' => 'interested',
        ])->assertOk()
            ->assertJsonPath('data.status', 'interested');

        $this->assertSame(
            1,
            EventRsvp::where('user_id', $user->id)->where('event_id', $event->id)->count()
        );
    }

    #[Test]
    public function delete_rsvp_removes_record(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        Sanctum::actingAs($user);

        EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'going',
        ]);

        $this->deleteJson("/api/v1/events/{$event->id}/rsvp")
            ->assertOk();

        $this->assertDatabaseMissing('event_rsvps', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    #[Test]
    public function rsvp_requires_valid_status(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/events/{$event->id}/rsvp", [
            'status' => 'maybe',
        ])->assertStatus(422);
    }
}
