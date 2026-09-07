<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $eventTitle, public string $eventDate)
    {
    }

    public function build()
    {
        return $this->subject('Reminder: ' . $this->eventTitle . ' is coming up')
            ->view('emails.event-reminder');
    }
}
