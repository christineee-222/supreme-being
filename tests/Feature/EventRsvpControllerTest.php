<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EventRsvpControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_create_an_rsvp_for_upcoming_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
            'status' => 'scheduled',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson("/events/{$event->id}/rsvps", [
                'status' => 'going',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('event_rsvps', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'going',
        ]);
    }

    #[Test]
    public function user_cannot_rsvp_twice_to_same_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
        ]);

        EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson("/events/{$event->id}/rsvps", [
                'status' => 'going',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function user_can_update_their_rsvp_before_event_starts(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
        ]);

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'interested',
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson("/events/{$event->id}/rsvps/{$rsvp->id}", [
                'status' => 'going',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('event_rsvps', [
            'id' => $rsvp->id,
            'status' => 'going',
        ]);
    }

    #[Test]
    public function user_cannot_update_someone_elses_rsvp(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
        ]);

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $owner->id,
            'event_id' => $event->id,
        ]);

        $response = $this
            ->actingAs($intruder)
            ->patchJson("/events/{$event->id}/rsvps/{$rsvp->id}", [
                'status' => 'going',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function user_can_delete_their_rsvp_before_event_starts(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
        ]);

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson("/events/{$event->id}/rsvps/{$rsvp->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('event_rsvps', [
            'id' => $rsvp->id,
        ]);
    }
}
