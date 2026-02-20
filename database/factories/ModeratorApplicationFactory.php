<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModeratorApplication>
 */
class ModeratorApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'motivation' => fake()->paragraph(),
            'scenario_response_1' => fake()->paragraph(),
            'scenario_response_2' => fake()->paragraph(),
            'conflicts_of_interest' => fake()->optional()->sentence(),
        ];
    }
}
