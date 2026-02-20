<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'workos_id' => 'fake-'.Str::random(10),
            'remember_token' => Str::random(10),
            'avatar' => '',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
        ]);
    }

    public function moderator(): static
    {
        return $this->state(fn () => [
            'role' => 'moderator',
        ]);
    }

    public function probationaryModerator(): static
    {
        return $this->state(fn () => [
            'role' => 'moderator',
            'is_moderator_probationary' => true,
        ]);
    }

    public function restricted(): static
    {
        return $this->state(fn () => [
            'restriction_ends_at' => now()->addDays(7),
        ]);
    }

    public function indefinitelyRestricted(): static
    {
        return $this->state(fn () => [
            'is_indefinitely_restricted' => true,
            'restriction_ends_at' => null,
        ]);
    }
}
