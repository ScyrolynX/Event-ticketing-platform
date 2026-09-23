<?php

namespace App\Http\Controllers;

use App\Services\TicketIssuingService;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function __construct(protected TicketIssuingService $ticketIssuer)
    {
    }

    public function handle(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $secretKey = config('services.paystack.secret_key');

        $computedSignature = hash_hmac('sha512', $request->getContent(), $secretKey);

        if ($signature !== $computedSignature) {
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
