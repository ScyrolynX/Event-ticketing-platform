<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function pendingOrder(User $buyer, int $quantity = 2): array
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
            'quantity_sold' => $quantity,
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'total_amount' => 100 * $quantity,
            'status' => 'pending',
            'paystack_reference' => (string) Str::uuid(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => $quantity,
            'unit_price' => 100,
        ]);

        return [$order, $ticketType];
    }

    public function test_a_buyer_can_cancel_their_own_unpaid_order_and_the_stock_is_released(): void
    {
        $buyer = User::factory()->create();
        [$order, $ticketType] = $this->pendingOrder($buyer, quantity: 2);

        $response = $this->actingAs($buyer, 'sanctum')->deleteJson("/api/v1/orders/{$order->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertSame(0, $ticketType->fresh()->quantity_sold);
    }

    public function test_cancelling_an_order_also_removes_its_order_items(): void
    {
        $buyer = User::factory()->create();
        [$order] = $this->pendingOrder($buyer);
        $itemId = $order->orderItems()->first()->id;

        $this->actingAs($buyer, 'sanctum')->deleteJson("/api/v1/orders/{$order->id}")->assertOk();

        $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
    }

    public function test_a_paid_order_cannot_be_cancelled_this_way(): void
    {
        $buyer = User::factory()->create();
        [$order, $ticketType] = $this->pendingOrder($buyer, quantity: 1);
        $order->update(['status' => 'paid']);

        $item = $order->orderItems()->first();
        Ticket::create(['order_item_id' => $item->id, 'unique_code' => (string) Str::uuid(), 'status' => 'valid']);

        $response = $this->actingAs($buyer, 'sanctum')->deleteJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
        $this->assertSame(1, $ticketType->fresh()->quantity_sold);
    }

    public function test_a_user_cannot_cancel_someone_elses_order(): void
    {
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        [$order] = $this->pendingOrder($buyer);

        $response = $this->actingAs($stranger, 'sanctum')->deleteJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
