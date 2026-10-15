<?php

namespace App\Http\Controllers;

use App\Services\TicketIssuingService;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function __construct(protected TicketIssuingService $ticketIssuer) {}

    public function handle(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $secretKey = config('services.paystack.secret_key');

        // Without a real secret, there is nothing meaningful to verify a
        // signature against — refuse rather than silently accept every
        // request. Sandbox installs issue tickets via completeDemoPayment()
        // instead, so this webhook has no legitimate caller until a real
        // Paystack secret key is configured.
        if (empty($secretKey)) {
            return response()->json(['message' => 'Webhook not configured'], 401);
        }

        if (empty($signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $computedSignature = hash_hmac('sha512', $request->getContent(), $secretKey);

        // hash_equals() is constant-time: a plain !== comparison leaks how
        // many leading bytes matched through response timing, letting an
        // attacker guess the signature one byte at a time.
        if (! hash_equals($computedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $reference = $request->input('data.reference');

        if ($event === 'charge.success') {
            $this->ticketIssuer->markOrderPaidAndIssueTicketsByReference($reference);
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
