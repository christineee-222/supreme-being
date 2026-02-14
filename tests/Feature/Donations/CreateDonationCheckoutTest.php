<?php

declare(strict_types=1);

namespace Tests\Feature\Donations;

use App\Models\Donation;
use App\Services\Stripe\CreateCheckoutSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateDonationCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_donation_and_returns_a_checkout_url(): void
    {
        $this->app->bind(CreateCheckoutSession::class, function () {
            return new class extends CreateCheckoutSession
            {
                public function __construct() {}

                public function forDonation(Donation $donation, int $amount, string $currency): array
                {
                    return [
                        'session_id' => 'cs_test_123',
                        'url' => 'https://example.test/checkout',
                    ];
                }
            };
        });

        $response = $this->postJson(route('donate.checkout'), [
            'amount' => 500,
            'currency' => 'usd',
        ]);

        $response->assertOk();
        $response->assertJson(['url' => 'https://example.test/checkout']);

        $this->assertDatabaseHas('donations', [
            'amount' => 500,
            'currency' => 'USD',
            'status' => 'pending',
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
    }

    public function test_it_validates_amount(): void
    {
        $response = $this->postJson(route('donate.checkout'), [
            'amount' => 50,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }
}
