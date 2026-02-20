<?php

namespace Database\Factories;

use App\Enums\ReportReason;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'reportable_type' => 'App\\Models\\Post',
            'reportable_id' => fake()->uuid(),
            'reason' => fake()->randomElement(ReportReason::cases())->value,
            'reporter_note' => fake()->optional()->sentence(),
        ];
    }
}
