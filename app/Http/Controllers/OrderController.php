<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\PaystackService;
use App\Services\TicketIssuingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderController extends Controller
{
    public function __construct(
        protected PaystackService $paystack,
        protected TicketIssuingService $ticketIssuer
    ) {
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $discountCode = null;

        if (!empty($validated['discount_code'])) {
            $discountCode = DiscountCode::where('code', $validated['discount_code'])->first();

            if (!$discountCode) {
                abort(422, 'Invalid discount code.');
            }
        }

        $order = DB::transaction(function () use ($validated, $user, $discountCode) {
            $ticketType = TicketType::where('id', $validated['ticket_type_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $remaining = $ticketType->quantity_available - $ticketType->quantity_sold;

            if ($validated['quantity'] > $remaining) {
                abort(422, 'Not enough tickets remaining for this ticket type.');
            }

            if ($discountCode && !$discountCode->isValidFor($ticketType->event_id)) {
                abort(422, 'This discount code is not valid for this event or has expired.');
            }

            $unitPrice = $discountCode ? $discountCode->apply($ticketType->price) : $ticketType->price;

            $ticketType->increment('quantity_sold', $validated['quantity']);

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $unitPrice * $validated['quantity'],
                'status' => 'pending',
                'paystack_reference' => (string) Str::uuid(),
            ]);

            $order->orderItems()->create([
                'ticket_type_id' => $ticketType->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
            ]);

            return $order;
        });

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

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('orderItems.ticketType.event', 'orderItems.tickets')
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'This order does not belong to you.');
        }

        return response()->json([
            'order' => $order->load('orderItems.ticketType.event', 'orderItems.tickets'),
        ]);
    }

    /**
     * Sandbox-mode payment completion. Stands in for a real Paystack redirect
     * when no live keys are configured: instead of leaving the buyer at a
     * dead end after checkout, this lets them finish the purchase from a
     * clearly-labeled "demo payment" screen. Runs the exact same
     * TicketIssuingService the real webhook uses, so the ticket/QR/check-in
     * flow downstream is identical either way.
     */
    public function completeDemoPayment(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'This order does not belong to you.');
        }

        if ($order->status === 'paid') {
            return response()->json([
                'message' => 'This order is already paid.',
                'order' => $order->load('orderItems.ticketType.event', 'orderItems.tickets'),
            ]);
        }

        if ($order->status !== 'pending') {
            abort(422, 'This order can no longer be paid.');
        }

        $order = $this->ticketIssuer->markOrderPaidAndIssueTickets($order);

        return response()->json([
            'message' => 'Payment complete (sandbox mode). Tickets issued.',
            'order' => $order->load('orderItems.ticketType.event', 'orderItems.tickets'),
        ]);
    }

    public function qrCode(Request $request, Ticket $ticket)
    {
        $owner = $ticket->orderItem->order->user_id;

        if ($owner !== $request->user()->id) {
            abort(403, 'This ticket does not belong to you.');
        }

        $svg = QrCode::size(200)->generate($ticket->unique_code);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'unique_code' => 'required|string',
        ]);

        $ticket = Ticket::where('unique_code', $validated['unique_code'])->first();

        if (! $ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        if ($ticket->status === 'used') {
            return response()->json(['message' => 'Ticket already used.'], 409);
        }

        $ticket->update(['status' => 'used']);

        return response()->json([
            'message' => 'Ticket accepted.',
            'ticket_id' => $ticket->id,
        ]);
    }
}
