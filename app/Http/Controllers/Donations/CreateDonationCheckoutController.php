<?php

declare(strict_types=1);

namespace App\Http\Controllers\Donations;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationCheckoutRequest;
use App\Models\Donation;
use App\Services\Stripe\CreateCheckoutSession;
use Illuminate\Http\JsonResponse;

final class CreateDonationCheckoutController extends Controller
{
    public function __invoke(
        StoreDonationCheckoutRequest $request,
        CreateCheckoutSession $createCheckoutSession,
    ): JsonResponse {
        $validated = $request->validated();

        $amount = (int) $validated['amount'];
        $currency = strtolower((string) ($validated['currency'] ?? 'usd'));

        $donation = Donation::create([
            'user_id' => $request->user()?->id,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 'pending',
        ]);

        $session = $createCheckoutSession->forDonation($donation, $amount, $currency);

        $donation->update([
            'stripe_checkout_session_id' => $session['session_id'],
        ]);

        return response()->json([
            'url' => $session['url'],
        ]);
    }
}
