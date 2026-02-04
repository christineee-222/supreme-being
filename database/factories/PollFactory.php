<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Poll;
use App\Models\User;
use App\Models\EssenceNumen;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PollFactory extends Factory
{
    protected $model = Poll::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'essence_numen_id' => EssenceNumen::query()->value('id'),
            'title' => $this->faker->sentence(4),
            'status' => 'draft',
        ];
    }

    public function published(): self
    {
        return $this->state(fn () => [
            'status' => 'published',
        ]);
    }
}



