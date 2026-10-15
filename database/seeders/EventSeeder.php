<?php

namespace Database\Seeders;

use App\Models\DiscountCode;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::first();

        // Two promoters so the settlement-report and promoter-management
        // screens have something to show on a fresh install, not just an
        // empty state.
        $promoterA = Promoter::create([
            'name' => 'Jollof Collective',
            'contact_email' => 'bookings@jollofcollective.test',
            'contact_phone' => '+233 20 000 0001',
            'commission_rate' => 15,
        ]);
        $promoterB = Promoter::create([
            'name' => 'Osu Nights Promotions',
            'contact_email' => 'hello@osunights.test',
            'contact_phone' => '+233 20 000 0002',
            'commission_rate' => 20,
        ]);

        $event3 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Accra Food & Wine Festival',
            'description' => 'A celebration of West African cuisine featuring top chefs, live cooking demos, and wine pairings.',
            'venue' => 'Aviation Social Centre, Accra',
            'event_date' => now()->addDays(10),
        ]);
        TicketType::create(['event_id' => $event3->id, 'name' => 'General Admission', 'price' => 80.00, 'quantity_available' => 300]);
        TicketType::create(['event_id' => $event3->id, 'name' => 'VIP Tasting', 'price' => 250.00, 'quantity_available' => 60]);

        $event4 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Startup Pitch Night',
            'description' => 'Early-stage founders pitch their startups to a panel of investors and mentors.',
            'venue' => 'MEST Africa, Accra',
            'event_date' => now()->addDays(18),
        ]);
        TicketType::create(['event_id' => $event4->id, 'name' => 'Attendee', 'price' => 30.00, 'quantity_available' => 150]);

        $event5 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Comedy Jam Live',
            'description' => 'An evening of stand-up comedy featuring Ghana\'s top comedians.',
            'venue' => 'National Theatre, Accra',
            'event_date' => now()->addDays(25),
        ]);
        TicketType::create(['event_id' => $event5->id, 'name' => 'Regular', 'price' => 70.00, 'quantity_available' => 250]);
        TicketType::create(['event_id' => $event5->id, 'name' => 'Front Row', 'price' => 180.00, 'quantity_available' => 40]);

        $event6 = Event::create([
            'organizer_id' => $organizer->id,
            'promoter_id' => $promoterB->id,
            'title' => 'Web3 & Blockchain Summit',
            'description' => 'Industry leaders discuss the future of blockchain technology in Africa.',
            'venue' => 'Kempinski Hotel, Accra',
            'event_date' => now()->addDays(30),
        ]);
        TicketType::create(['event_id' => $event6->id, 'name' => 'Standard Pass', 'price' => 150.00, 'quantity_available' => 200]);
        TicketType::create(['event_id' => $event6->id, 'name' => 'All-Access Pass', 'price' => 400.00, 'quantity_available' => 50]);

        $event7 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Sunday Jazz Brunch',
            'description' => 'Live jazz performances paired with a curated brunch menu in a relaxed outdoor setting.',
            'venue' => 'Labadi Beach Hotel, Accra',
            'event_date' => now()->addDays(5),
        ]);
        TicketType::create(['event_id' => $event7->id, 'name' => 'Brunch + Show', 'price' => 220.00, 'quantity_available' => 80]);

        $event8 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Accra Marathon Expo',
            'description' => 'A pre-race expo featuring gear vendors, nutrition talks, and athlete meet and greets ahead of the annual Accra Marathon.',
            'venue' => 'Independence Square, Accra',
            'event_date' => now()->addDays(12),
        ]);
        TicketType::create(['event_id' => $event8->id, 'name' => 'General Entry', 'price' => 20.00, 'quantity_available' => 500]);

        $event9 = Event::create([
            'organizer_id' => $organizer->id,
            'promoter_id' => $promoterA->id,
            'title' => 'Fashion Week Accra',
            'description' => 'A runway showcase featuring emerging and established West African fashion designers.',
            'venue' => 'Movenpick Ambassador Hotel, Accra',
            'event_date' => now()->addDays(20),
        ]);
        TicketType::create(['event_id' => $event9->id, 'name' => 'General', 'price' => 100.00, 'quantity_available' => 200]);
        TicketType::create(['event_id' => $event9->id, 'name' => 'Front Row VIP', 'price' => 350.00, 'quantity_available' => 30]);

        $event10 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Gospel Night Live',
            'description' => 'An evening of praise and worship featuring some of Ghana\'s leading gospel artists.',
            'venue' => 'Perez Dome, Accra',
            'event_date' => now()->addDays(15),
        ]);
        TicketType::create(['event_id' => $event10->id, 'name' => 'Regular', 'price' => 40.00, 'quantity_available' => 400]);
        TicketType::create(['event_id' => $event10->id, 'name' => 'VIP', 'price' => 150.00, 'quantity_available' => 60]);

        // Two discount codes so checkout's discount-code path has something
        // real to test against without the box office having to create one
        // first: one sitewide, one scoped to a single event.
        DiscountCode::create([
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10,
            'event_id' => null,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonths(3),
        ]);
        DiscountCode::create([
            'code' => 'JAZZ20',
            'type' => 'fixed',
            'value' => 20,
            'event_id' => $event7->id,
            'valid_from' => now()->subDay(),
            'valid_until' => $event7->event_date,
        ]);
    }
}
