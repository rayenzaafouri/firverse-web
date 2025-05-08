<?php

namespace App\Service;

use Stripe\StripeClient;

class StripeService
{
    private $stripe;
    
    public function __construct(string $secretKey)
    {
        $this->stripe = new StripeClient($secretKey);
    }
    
    public function createPaymentIntent(float $amount, string $currency = 'usd')
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amount * 100, // Stripe uses cents
            'currency' => $currency,
        ]);
    }
    
}