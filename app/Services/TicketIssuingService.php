<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TicketIssuingService
{
    /**
     * Single source of truth for "an order was paid" — called from the real
     * Paystack webhook AND from the sandbox-mode "Complete Payment" button.
     * Idempotent: safe to call more than once for the same order (Paystack
     * retries webhooks, and a user could double-click the demo button).
     */
    public function markOrderPaidAndIssueTickets(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::where('id', $order->id)
                ->lockForUpdate()
                ->with('orderItems.tickets')
                ->first();

            if (! $order || $order->status === 'paid') {
                return $order;
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

            return $order->fresh(['orderItems.tickets']);
        });
    }

    public function markOrderPaidAndIssueTicketsByReference(string $reference): void
    {
        $order = Order::where('paystack_reference', $reference)->first();

        if (! $order) {
            return;
        }

        $this->markOrderPaidAndIssueTickets($order);
    }
}
