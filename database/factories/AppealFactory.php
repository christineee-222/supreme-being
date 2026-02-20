<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appeal>
 */
class AppealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->indefinitelyRestricted(),
            'appeal_number' => 1,
            'user_statement' => fake()->paragraph(),
            'submitted_at' => now(),
            'eligible_from' => now()->subDay(),
        ];
    }
}
