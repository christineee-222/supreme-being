<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Events/Show')
                ->has('event')
                ->has('userRsvp')
            );
    }

    public function test_event_show_includes_users_rsvp_when_it_exists(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $rsvp = EventRsvp::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Events/Show')
                ->has('userRsvp', fn (Assert $rsvpProp) => $rsvpProp
                    ->where('id', $rsvp->id)
                    ->where('status', $rsvp->status)
                    ->where('user_id', $user->id)
                    ->where('event_id', $event->id)
                )
            );
    }

    public function test_event_show_returns_null_rsvp_when_user_has_not_rsvped(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Events/Show')
                ->where('userRsvp', null)
            );
    }
}
