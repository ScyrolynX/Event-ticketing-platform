<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventManagementController extends Controller
{
    public function index()
    {
        $events = Event::with('ticketTypes')->latest()->get();

        return response()->json(['events' => $events]);
    }

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

    /**
     * Edit an existing event. Sent as POST with _method=PATCH so file
     * uploads work (PHP does not populate $_FILES on real PUT/PATCH
     * requests) — Laravel's method-spoofing makes this transparent.
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

        return response()->json(['event' => $event->fresh()]);
    }

    /**
     * Only deletable while zero tickets have sold — an event with paid
     * customers holding real tickets can't just vanish. They should be
     * refunded (see RefundController) before the event itself is removed.
     */
    public function destroy(Event $event)
    {
        $totalSold = $event->ticketTypes()->sum('quantity_sold');

        if ($totalSold > 0) {
            abort(422, 'This event has tickets already sold. Refund those orders before deleting the event.');
        }

        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->ticketTypes()->delete();
        $event->delete();

        return response()->json(['message' => 'Event deleted.']);
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

    public function updateTicketType(Request $request, Event $event, TicketType $ticketType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity_available' => 'required|integer|min:' . $ticketType->quantity_sold,
        ]);

        $ticketType->update($validated);

        return response()->json(['ticket_type' => $ticketType->fresh()]);
    }

    public function destroyTicketType(Event $event, TicketType $ticketType)
    {
        if ($ticketType->quantity_sold > 0) {
            abort(422, 'This ticket type has already sold tickets and cannot be deleted.');
        }

        $ticketType->delete();

        return response()->json(['message' => 'Ticket type deleted.']);
    }
}
