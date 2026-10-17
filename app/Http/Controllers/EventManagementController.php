<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventManagementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:concert,conference,sports,other',
            'promoter_id' => 'nullable|exists:promoters,id',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'event_date' => 'required|date',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('events', 'public')
            : null;

        $event = Event::create([
            'organizer_id' => $request->user()->id,
            'promoter_id' => $validated['promoter_id'] ?? null,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'venue' => $validated['venue'],
            'event_date' => $validated['event_date'],
            'image_path' => $imagePath,
        ]);

        return response()->json(['event' => $event], 201);
    }

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

    public function index()
    {
        $events = Event::with('ticketTypes')->latest()->get();

        return response()->json(['events' => $events]);
    }

    /**
     * Edit an existing event. A new image replaces (and deletes) the old
     * one; leaving the image field out keeps whatever is already there.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:concert,conference,sports,other',
            'promoter_id' => 'nullable|exists:promoters,id',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'event_date' => 'required|date',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('events', 'public');
        }
        unset($validated['image']);

        $event->update($validated);

        return response()->json(['event' => $event->fresh('ticketTypes')]);
    }

    /**
     * Delete an event — but never one that has already sold tickets.
     * ticket_types and order_items cascade-delete at the DB level, which
     * would silently wipe real purchase history, so that path is blocked
     * entirely rather than made "safe" after the fact.
     */
    public function destroy(Event $event)
    {
        $alreadySold = $event->ticketTypes()->where('quantity_sold', '>', 0)->exists();

        if ($alreadySold) {
            return response()->json([
                'message' => 'This event already has ticket sales, so it can\'t be deleted. Edit it instead, or stop selling by setting ticket quantities to 0.',
            ], 422);
        }

        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted.']);
    }

    public function updateTicketType(Request $request, Event $event, TicketType $ticketType)
    {
        if ($ticketType->event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity_available' => 'required|integer|min:'.$ticketType->quantity_sold,
        ]);

        $ticketType->update($validated);

        return response()->json(['ticket_type' => $ticketType]);
    }

    /**
     * Delete a ticket type — but never one that has already sold tickets,
     * for the same reason an event with sales can't be deleted.
     */
    public function destroyTicketType(Event $event, TicketType $ticketType)
    {
        if ($ticketType->event_id !== $event->id) {
            abort(404);
        }

        if ($ticketType->quantity_sold > 0) {
            return response()->json([
                'message' => 'This ticket type already has sales, so it can\'t be deleted.',
            ], 422);
        }

        $ticketType->delete();

        return response()->json(['message' => 'Ticket type deleted.']);
    }
}
