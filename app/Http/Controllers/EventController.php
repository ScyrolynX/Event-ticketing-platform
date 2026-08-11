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
}
