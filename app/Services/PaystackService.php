<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    /**
     * Initialize a payment transaction with Paystack.
     * Sends the amount and customer email, receives an authorization URL
     * the customer is redirected to in order to complete payment.
     */
    public function initializeTransaction(string $email, float $amount, string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $email,
                'amount' => $amount * 100, // Paystack expects amount in kobo/pesewas
                'reference' => $reference,
            ]);

        return $response->json();
    }

    /**
     * Verify a transaction after payment, using the reference
     * returned during initialization, to confirm it was actually successful.
     */
    public function verifyTransaction(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        return $response->json();
    }
}
