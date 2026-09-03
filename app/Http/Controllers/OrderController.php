<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderController extends Controller
{
    public function __construct(protected PaystackService $paystack)
    {
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $order = DB::transaction(function () use ($validated, $user) {
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
            ->with('orderItems.ticketType', 'orderItems.tickets')
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
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

    /**
     * Staff-facing check-in (spec section 4.4): scan a ticket's unique_code,
     * confirm it's valid and unused, mark it used. Only reachable by staff
     * with the Admin, Event Manager, or Box Office role, enforced by route
     * middleware, not by a check in here.
     */
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
