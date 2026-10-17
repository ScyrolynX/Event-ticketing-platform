<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoPaymentGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function pendingOrder(User $buyer): Order
    {
        $organizer = User::factory()->create();

        $event = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Test Event',
            'venue' => 'Test Venue',
            'event_date' => now()->addDays(5),
        ]);

        $ticketType = TicketType::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 100,
            'quantity_available' => 10,
            'quantity_sold' => 1,
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'paystack_reference' => (string) Str::uuid(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        return $order;
    }

    public function test_demo_payment_works_when_no_live_paystack_key_is_configured(): void
    {
        config(['services.paystack.secret_key' => null]);
        $buyer = User::factory()->create();
        $order = $this->pendingOrder($buyer);

        $response = $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/complete-demo-payment");

        $response->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_demo_payment_is_blocked_once_a_live_paystack_key_is_configured(): void
    {
        config(['services.paystack.secret_key' => 'sk_live_fake_key_for_test']);
        $buyer = User::factory()->create();
        $order = $this->pendingOrder($buyer);

        $response = $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/complete-demo-payment");

        // 404, not 403 — this "pay for free" shortcut shouldn't even hint
        // that it exists once real payments are possible.
        $response->assertStatus(404);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_a_user_cannot_complete_demo_payment_for_someone_elses_order(): void
    {
        config(['services.paystack.secret_key' => null]);
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->pendingOrder($buyer);

        $response = $this->actingAs($stranger, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/complete-demo-payment");

        $response->assertStatus(403);
        $this->assertSame('pending', $order->fresh()->status);
    }
}
