<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\TicketType;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(protected PaystackService $paystack)
    {
    }

    /**
     * Create a pending order for a ticket type + quantity, reserve the stock
     * atomically, then hand the customer off to Paystack to pay.
     */
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $order = DB::transaction(function () use ($validated, $user) {
            // Lock the row so two concurrent purchases can't both succeed
            // past the remaining stock (spec section 7: enforce quantity
            // limits atomically).
            $ticketType = TicketType::where('id', $validated['ticket_type_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $remaining = $ticketType->quantity_available - $ticketType->quantity_sold;

            if ($validated['quantity'] > $remaining) {
                abort(422, 'Not enough tickets remaining for this ticket type.');
            }

            $ticketType->increment('quantity_sold', $validated['quantity']);

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $ticketType->price * $validated['quantity'],
                'status' => 'pending',
                'paystack_reference' => (string) Str::uuid(),
            ]);

            $order->orderItems()->create([
                'ticket_type_id' => $ticketType->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $ticketType->price,
            ]);

            return $order;
        });

        // Only after the order is safely persisted do we talk to Paystack —
        // if this call fails the order still exists as "pending" and can be retried.
        $payment = $this->paystack->initializeTransaction(
            $user->email,
            $order->total_amount,
            $order->paystack_reference
        );

        return response()->json([
            'order' => $order->load('orderItems'),
            'authorization_url' => $payment['data']['authorization_url'] ?? null,
        ]);
    }

    /**
     * The logged-in customer's own order history, with ticket types and
     * any issued tickets included (spec section 4.3: "My tickets / order history").
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('orderItems.ticketType', 'orderItems.tickets')
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }
}
