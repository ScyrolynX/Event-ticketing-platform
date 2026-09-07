<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Email customers holding paid tickets for events happening in the next 24 hours.';

    public function handle()
    {
        $events = Event::whereBetween('event_date', [now(), now()->addDay()])->get();

        foreach ($events as $event) {
            $orders = \App\Models\Order::where('status', 'paid')
                ->whereHas('orderItems.ticketType', fn ($q) => $q->where('event_id', $event->id))
                ->with('user')
                ->get();

            foreach ($orders as $order) {
                Mail::to($order->user->email)->send(
                    new EventReminderMail($order, $event->title, $event->event_date->format('F j, Y g:i A'))
                );
            }

            $this->info("Sent reminders for: {$event->title} ({$orders->count()} orders)");
        }
    }
}
