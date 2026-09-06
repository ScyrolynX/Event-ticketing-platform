<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * A customer requests a refund on their own order.
     * Ownership is checked here, not just trusted from the request.
     */
    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'This order does not belong to you.');
        }

        if ($order->status !== 'paid') {
            abort(422, 'Only paid orders can be refunded.');
        }

        if ($order->refundRequests()->whereIn('status', ['pending', 'approved'])->exists()) {
            abort(422, 'A refund request already exists for this order.');
        }

        $validated = $request->validate(['reason' => 'nullable|string|max:500']);

        $refund = $order->refundRequests()->create([
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['refund_request' => $refund], 201);
    }

    /**
     * Staff-facing list of every refund request, for the backoffice screen.
     */
    public function index()
    {
        $refunds = RefundRequest::with('order.user', 'order.orderItems.ticketType')
            ->latest()
            ->get();

        return response()->json(['refunds' => $refunds]);
    }

    /**
     * Approve a refund: releases the ticket stock back to sale, invalidates
     * any issued tickets, and marks the order refunded. Wrapped in a
     * transaction so a partial failure can't leave stock or tickets
     * inconsistent with the order's real state.
     */
    public function approve(RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'pending') {
            abort(422, 'This refund request has already been processed.');
        }

        DB::transaction(function () use ($refundRequest) {
            $order = $refundRequest->order;

            foreach ($order->orderItems as $item) {
                $item->ticketType()->decrement('quantity_sold', $item->quantity);
                $item->tickets()->update(['status' => 'cancelled']);
            }

            $order->update(['status' => 'refunded']);
            $refundRequest->update(['status' => 'approved']);
        });

        return response()->json(['message' => 'Refund approved.']);
    }

    public function reject(RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'pending') {
            abort(422, 'This refund request has already been processed.');
        }

        $refundRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Refund rejected.']);
    }
}
