<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Event;
use App\Models\EventRsvp;

class EventRsvpPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_rsvp_to_upcoming_event()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
            'status' => 'scheduled',
        ]);

        $this->assertTrue(
            Gate::forUser($user)->allows('create', [EventRsvp::class, $event])
        );
    }

    #[Test]
    public function user_cannot_rsvp_to_event_that_has_started()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->subMinute(),
            'status' => 'scheduled',
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('create', [EventRsvp::class, $event])
        );
    }

    #[Test]
    public function user_cannot_rsvp_to_cancelled_event()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
            'status' => 'cancelled',
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('create', [EventRsvp::class, $event])
        );
    }

    #[Test]
    public function user_cannot_rsvp_more_than_once_to_same_event()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
        ]);

        EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('create', [EventRsvp::class, $event])
        );
    }

    #[Test]
    public function user_can_update_their_own_rsvp_before_event_starts()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->addHour(),
        ]);

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->assertTrue(
            Gate::forUser($user)->allows('update', $rsvp)
        );
    }

    #[Test]
    public function user_cannot_update_rsvp_after_event_starts()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->subMinute(),
        ]);

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('update', $rsvp)
        );
    }

    #[Test]
    public function user_cannot_delete_rsvp_after_event_starts()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->subMinute(),
        ]);

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('delete', $rsvp)
        );
    }

    #[Test]
    public function admin_can_manage_rsvps_anytime()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $event = Event::factory()->create([
            'starts_at' => now()->subMinute(),
            'status' => 'cancelled',
        ]);

        $rsvp = EventRsvp::factory()->create([
            'event_id' => $event->id,
        ]);

        $this->assertTrue(
            Gate::forUser($admin)->allows('update', $rsvp)
        );

        $this->assertTrue(
            Gate::forUser($admin)->allows('delete', $rsvp)
        );
    }
}


