<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventRsvpFactory extends Factory
{
    protected $model = EventRsvp::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'status' => 'going',
        ];
    }
}

