<?php

namespace App\Http\Controllers;

use App\Models\Promoter;
use Illuminate\Http\Request;

class PromoterController extends Controller
{
    public function index()
    {
        return response()->json(['promoters' => Promoter::withCount('events')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $promoter = Promoter::create($validated);

        return response()->json(['promoter' => $promoter], 201);
    }

    /**
     * Settlement report (spec 4.2): per promoter, tickets sold and the
     * amount owed to the promoter after the company's commission.
     */
    public function settlementReport()
    {
        $promoters = Promoter::with('events.ticketTypes', 'events.ticketTypes.orderItems')->get();

        $report = $promoters->map(function ($promoter) {
            $ticketsSold = 0;
            $grossRevenue = 0;

            foreach ($promoter->events as $event) {
                foreach ($event->ticketTypes as $ticketType) {
                    $ticketsSold += $ticketType->quantity_sold;
                    $grossRevenue += $ticketType->orderItems->sum(fn ($item) => $item->unit_price * $item->quantity);
                }
            }

            $commission = $grossRevenue * ($promoter->commission_rate / 100);
            $owed = $grossRevenue - $commission;

            return [
                'promoter_id' => $promoter->id,
                'name' => $promoter->name,
                'commission_rate' => $promoter->commission_rate,
                'tickets_sold' => $ticketsSold,
                'gross_revenue' => $grossRevenue,
                'commission_kept' => $commission,
                'amount_owed' => $owed,
            ];
        });

        return response()->json(['report' => $report]);
    }
}
