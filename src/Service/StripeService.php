<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// Service that uses Stripe to simulate a purchase (test/sandbox mode).
class StripeService
{
    public function __construct(
        #[Autowire(env: 'STRIPE_SECRET_KEY')]
        private readonly string $stripeSecretKey,
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    public function createCheckoutSession(
        string $productName,
        float $amount,
        string $successUrl,
        string $cancelUrl,
    ): Session {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $productName,
                    ],
                    // Stripe expects an amount in cents
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
        ]);
    }

    // Checks with Stripe that the given checkout session was really paid,
    // instead of trusting the browser redirect alone.
    public function isSessionPaid(string $sessionId): bool
    {
        try {
            $session = Session::retrieve($sessionId);
        } catch (ApiErrorException) {
            return false;
        }

        return $session->payment_status === 'paid';
    }
}
