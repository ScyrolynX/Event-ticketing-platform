<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PaystackWebhookController extends Controller
{
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
            $this->markOrderPaidAndIssueTickets($reference);
        }

        return response()->json(['message' => 'Webhook received']);
    }

    /**
     * The webhook is the only source of truth for a successful payment
     * (spec section 7) — tickets are generated here, never on the strength
     * of a browser redirect. Paystack can retry webhooks, so this must be
     * safe to run more than once for the same reference.
     */
    protected function markOrderPaidAndIssueTickets(string $reference): void
    {
        DB::transaction(function () use ($reference) {
            $order = Order::where('paystack_reference', $reference)
                ->lockForUpdate()
                ->with('orderItems.tickets')
                ->first();

            if (! $order || $order->status === 'paid') {
                // Unknown reference, or we've already processed this
                // webhook once before — do nothing, so a retry can't
                // double-issue tickets.
                return;
            }

            $order->update(['status' => 'paid']);

            foreach ($order->orderItems as $orderItem) {
                $alreadyIssued = $orderItem->tickets->count();

                for ($i = $alreadyIssued; $i < $orderItem->quantity; $i++) {
                    Ticket::create([
                        'order_item_id' => $orderItem->id,
                        'unique_code' => Crypt::encryptString(
                            $orderItem->id . ':' . $i . ':' . Str::uuid()
                        ),
                        'status' => 'valid',
                    ]);
                }
            }
        });
    }
}