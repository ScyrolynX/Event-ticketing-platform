<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Sales dashboard (spec 4.2): tickets sold, revenue, and remaining
     * inventory, broken down per event.
     */
    public function index()
    {
        $events = Event::with('ticketTypes')->get();

        $breakdown = $events->map(function ($event) {
            $sold = $event->ticketTypes->sum('quantity_sold');
            $available = $event->ticketTypes->sum('quantity_available');

            $revenue = Order::where('status', 'paid')
                ->whereHas('orderItems.ticketType', fn ($q) => $q->where('event_id', $event->id))
                ->with('orderItems.ticketType')
                ->get()
                ->flatMap->orderItems
                ->filter(fn ($item) => $item->ticketType->event_id === $event->id)
                ->sum(fn ($item) => $item->unit_price * $item->quantity);

            return [
                'event_id' => $event->id,
                'title' => $event->title,
                'tickets_sold' => $sold,
                'tickets_remaining' => $available - $sold,
                'revenue' => $revenue,
            ];
        });

        return response()->json([
            'total_revenue' => $breakdown->sum('revenue'),
            'total_tickets_sold' => $breakdown->sum('tickets_sold'),
            'events' => $breakdown,
        ]);
    }
}
