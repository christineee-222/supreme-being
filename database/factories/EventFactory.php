<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startsAt = Carbon::now()->addDays(2);

        return [
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'starts_at'   => $startsAt,
            'ends_at'     => (clone $startsAt)->addHours(2),
            'status'      => 'scheduled',
            'user_id'     => User::factory(),
        ];
    }

    /**
     * Event that has already started
     */
    public function started(): static
    {
        $startsAt = Carbon::now()->subHour();

        return $this->state(fn () => [
            'starts_at' => $startsAt,
            'ends_at'   => (clone $startsAt)->addHours(2),
        ]);
    }

    /**
     * Cancelled event
     */
    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
        ]);
    }
}
