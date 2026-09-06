<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;

class EventManagementController extends Controller
{
    /**
     * Create a new event (spec 4.1). Only Admin/Event Manager can reach
     * this route, enforced by role middleware, not code here.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'event_date' => 'required|date',
        ]);

        $event = Event::create([
            'organizer_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'venue' => $validated['venue'],
            'event_date' => $validated['event_date'],
        ]);

        return response()->json(['event' => $event], 201);
    }

    /**
     * Add a ticket type to an event.
     */
    public function storeTicketType(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity_available' => 'required|integer|min:1',
        ]);

        $ticketType = $event->ticketTypes()->create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity_available' => $validated['quantity_available'],
            'quantity_sold' => 0,
        ]);

        return response()->json(['ticket_type' => $ticketType], 201);
    }

    /**
     * List all events for the backoffice management screen.
     */
    public function index()
    {
        $events = Event::with('ticketTypes')->latest()->get();

        return response()->json(['events' => $events]);
    }
}
