<?php

namespace App\Http\Controllers;

use App\Models\Event;

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

    /**
     * JSON version for the API (used by React and any other API consumer).
     * The Blade-rendering index()/show() above stay untouched for the
     * existing working site.
     */
    public function apiIndex()
    {
        $events = Event::with('ticketTypes')->get();

        return response()->json(['events' => $events]);
    }

    public function apiShow(Event $event)
    {
        $event->load('ticketTypes');

        return response()->json(['event' => $event]);
    }
}
