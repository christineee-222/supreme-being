<?php

declare(strict_types=1);

namespace App\Http\Controllers\Donations;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

final class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature');
        $secret = (string) config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature failed');

            return response('Invalid signature', 400);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook payload invalid');

            return response('Invalid payload', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $donationId = $session->metadata->donation_id ?? null;

            $donation = null;

            if ($donationId) {
                $donation = Donation::find((int) $donationId);
            }

            if (! $donation) {
                $donation = Donation::where('stripe_checkout_session_id', (string) $session->id)->first();
            }

            if ($donation && ! $donation->stripe_webhook_event_id) {
                $donation->update([
                    'status' => 'succeeded',
                    'stripe_payment_intent_id' => $session->payment_intent ?? null,
                    'stripe_webhook_event_id' => (string) $event->id,
                ]);
            }
        }

        return response('ok', 200);
    }
}
