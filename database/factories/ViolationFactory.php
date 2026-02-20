<?php

namespace Database\Factories;

use App\Enums\ViolationConsequence;
use App\Models\ModeratorDecision;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Violation>
 */
class ViolationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'report_id' => Report::factory(),
            'moderator_decision_id' => ModeratorDecision::factory(),
            'confirmed_by' => User::factory()->moderator(),
            'rule_reference' => 'R-'.fake()->numberBetween(1, 20),
            'violation_number' => 1,
            'consequence_applied' => fake()->randomElement(ViolationConsequence::cases())->value,
            'moderator_note' => fake()->optional()->sentence(),
        ];
    }
}
