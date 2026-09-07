<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('ticketTypes')->get();

        return view('events.index', ['events' => $events]);
    }

    public function show(Event $event)
    {
        $event->load('ticketTypes');

        return view('events.show', ['event' => $event]);
    }

    public function apiIndex(Request $request)
    {
        $query = Event::with('ticketTypes');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        $events = $query->get();

        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->max_price;
            $events = $events->filter(function ($event) use ($maxPrice) {
                return $event->ticketTypes->min('price') <= $maxPrice;
            })->values();
        }

        return response()->json(['events' => $events]);
    }

    public function apiShow(Event $event)
    {
        $event->load('ticketTypes');

        return response()->json(['event' => $event]);
    }
}
