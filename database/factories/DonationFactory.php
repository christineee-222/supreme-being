<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(5, 5000),
            'currency' => 'USD',
            'status' => 'completed',
            // essence_numen_id is intentionally omitted
            // it is created automatically in Donation::booted()
        ];
    }
}
