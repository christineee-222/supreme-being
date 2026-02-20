<?php

namespace Database\Factories;

use App\Enums\ModeratorDecisionType;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModeratorDecision>
 */
class ModeratorDecisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'moderator_id' => User::factory()->moderator(),
            'report_id' => Report::factory(),
            'decision' => fake()->randomElement(ModeratorDecisionType::cases())->value,
        ];
    }
}
