<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    /**
     * If no real Paystack key is configured (local dev), skip the network
     * call entirely and return immediately — no point waiting on a
     * connection that can never succeed.
     */
    public function initializeTransaction(string $email, float $amount, string $reference): array
    {
        if (empty($this->secretKey)) {
            Log::info('Paystack not configured, skipping initializeTransaction.');
            return [];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->connectTimeout(1)
                ->timeout(2)
                ->post("{$this->baseUrl}/transaction/initialize", [
                    'email' => $email,
                    'amount' => $amount * 100,
                    'reference' => $reference,
                ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('Paystack initializeTransaction failed: ' . $e->getMessage());
            return [];
        }
    }

    public function verifyTransaction(string $reference): array
    {
        if (empty($this->secretKey)) {
            Log::info('Paystack not configured, skipping verifyTransaction.');
            return [];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->connectTimeout(1)
                ->timeout(2)
                ->get("{$this->baseUrl}/transaction/verify/{$reference}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('Paystack verifyTransaction failed: ' . $e->getMessage());
            return [];
        }
    }
}
