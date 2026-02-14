<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\Donation;
use Stripe\StripeClient;

class CreateCheckoutSession
{
    public function __construct(
        public StripeClient $stripe
    ) {}

    /**
     * @return array{session_id: string, url: string}
     */
    public function forDonation(Donation $donation, int $amount, string $currency): array
    {
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => 'Donation',
                    ],
                ],
            ]],
            'success_url' => url('/donate/success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/donate'),
            'client_reference_id' => (string) $donation->id,
            'metadata' => [
                'donation_id' => (string) $donation->id,
                'user_id' => (string) ($donation->user_id ?? ''),
            ],
        ]);

        return [
            'session_id' => (string) $session->id,
            'url' => (string) $session->url,
        ];
    }
}
