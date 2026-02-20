<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModeratorPerformanceReview>
 */
class ModeratorPerformanceReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'moderator_id' => User::factory()->moderator(),
            'report_id' => Report::factory(),
        ];
    }
}
