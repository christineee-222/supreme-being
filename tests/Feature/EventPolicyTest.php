<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


final class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function owner_can_update_event_before_it_starts(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $this->assertTrue(
            Gate::forUser($user)->allows('update', $event)
        );
    }

    #[Test]
    public function owner_cannot_update_event_after_it_starts(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => now()->subMinute(),
            'status' => 'scheduled',
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('update', $event)
        );
    }

    #[Test]
    public function owner_can_cancel_event_before_it_starts(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => now()->addHour(),
            'status' => 'scheduled',
        ]);

        $this->assertTrue(
            Gate::forUser($user)->allows('cancel', $event)
        );
    }

    #[Test]
    public function owner_cannot_cancel_event_after_it_starts(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => now()->subMinute(),
            'status' => 'scheduled',
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('cancel', $event)
        );
    }

    #[Test]
    public function events_cannot_be_deleted_by_anyone(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertFalse(
            Gate::forUser($user)->allows('delete', $event)
        );
    }

    #[Test]
    public function admin_can_update_and_cancel_events_anytime(): void
    {
        $admin = User::factory()->admin()->create();

        $event = Event::factory()->create([
            'starts_at' => now()->subDay(),
            'status' => 'scheduled',
        ]);

        $this->assertTrue(
            Gate::forUser($admin)->allows('update', $event)
        );

        $this->assertTrue(
            Gate::forUser($admin)->allows('cancel', $event)
        );
    }
}

