<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\TicketType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function manager(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Event Manager');

        return $user;
    }

    protected function eventWithTicketType(int $quantitySold = 0): array
    {
        $organizer = User::factory()->create();

        $event = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Original Title',
            'category' => 'concert',
            'venue' => 'Original Venue',
            'event_date' => now()->addDays(10),
        ]);

        $ticketType = TicketType::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 50,
            'quantity_available' => 10,
            'quantity_sold' => $quantitySold,
        ]);

        return [$event, $ticketType];
    }

    // --- Event update -------------------------------------------------

    public function test_a_manager_can_update_an_event(): void
    {
        $manager = $this->manager();
        [$event] = $this->eventWithTicketType();

        $response = $this->actingAs($manager, 'sanctum')->postJson("/api/v1/manage/events/{$event->id}", [
            'title' => 'Updated Title',
            'category' => 'conference',
            'venue' => 'New Venue',
            'event_date' => now()->addDays(20)->toDateTimeString(),
        ]);

        $response->assertOk()->assertJsonPath('event.title', 'Updated Title');
        $this->assertSame('New Venue', $event->fresh()->venue);
    }

    public function test_updating_an_event_with_a_new_image_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $manager = $this->manager();
        [$event] = $this->eventWithTicketType();

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('events', 'public');
        $event->update(['image_path' => $oldPath]);

        $response = $this->actingAs($manager, 'sanctum')->postJson("/api/v1/manage/events/{$event->id}", [
            'title' => $event->title,
            'category' => $event->category,
            'venue' => $event->venue,
            'event_date' => $event->event_date,
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertOk();
        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNotNull($event->fresh()->image_path);
        $this->assertNotSame($oldPath, $event->fresh()->image_path);
    }

    public function test_a_plain_customer_cannot_update_an_event(): void
    {
        $customer = User::factory()->create();
        [$event] = $this->eventWithTicketType();

        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/manage/events/{$event->id}", [
            'title' => 'Hijacked',
            'category' => 'concert',
            'venue' => $event->venue,
            'event_date' => $event->event_date,
        ]);

        $response->assertStatus(403);
        $this->assertSame('Original Title', $event->fresh()->title);
    }

    // --- Event delete ---------------------------------------------------

    public function test_a_manager_can_delete_an_event_with_no_sales(): void
    {
        $manager = $this->manager();
        [$event] = $this->eventWithTicketType(quantitySold: 0);

        $response = $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/manage/events/{$event->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_an_event_with_sales_cannot_be_deleted(): void
    {
        $manager = $this->manager();
        [$event] = $this->eventWithTicketType(quantitySold: 1);

        $response = $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/manage/events/{$event->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    // --- Ticket type update/delete --------------------------------------

    public function test_a_manager_can_update_a_ticket_type(): void
    {
        $manager = $this->manager();
        [$event, $ticketType] = $this->eventWithTicketType();

        $response = $this->actingAs($manager, 'sanctum')->patchJson(
            "/api/v1/manage/events/{$event->id}/ticket-types/{$ticketType->id}",
            ['name' => 'VIP', 'price' => 150, 'quantity_available' => 20]
        );

        $response->assertOk()->assertJsonPath('ticket_type.name', 'VIP');
    }

    public function test_quantity_available_cannot_drop_below_quantity_sold(): void
    {
        $manager = $this->manager();
        [$event, $ticketType] = $this->eventWithTicketType(quantitySold: 5);

        $response = $this->actingAs($manager, 'sanctum')->patchJson(
            "/api/v1/manage/events/{$event->id}/ticket-types/{$ticketType->id}",
            ['name' => 'General', 'price' => 50, 'quantity_available' => 3]
        );

        $response->assertStatus(422);
        $this->assertSame(10, $ticketType->fresh()->quantity_available);
    }

    public function test_a_ticket_type_from_another_event_returns_404(): void
    {
        $manager = $this->manager();
        [$eventA] = $this->eventWithTicketType();
        [, $ticketTypeB] = $this->eventWithTicketType();

        $response = $this->actingAs($manager, 'sanctum')->patchJson(
            "/api/v1/manage/events/{$eventA->id}/ticket-types/{$ticketTypeB->id}",
            ['name' => 'General', 'price' => 50, 'quantity_available' => 10]
        );

        $response->assertStatus(404);
    }

    public function test_a_manager_can_delete_a_ticket_type_with_no_sales(): void
    {
        $manager = $this->manager();
        [$event, $ticketType] = $this->eventWithTicketType(quantitySold: 0);

        $response = $this->actingAs($manager, 'sanctum')
            ->deleteJson("/api/v1/manage/events/{$event->id}/ticket-types/{$ticketType->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('ticket_types', ['id' => $ticketType->id]);
    }

    public function test_a_ticket_type_with_sales_cannot_be_deleted(): void
    {
        $manager = $this->manager();
        [$event, $ticketType] = $this->eventWithTicketType(quantitySold: 2);

        $response = $this->actingAs($manager, 'sanctum')
            ->deleteJson("/api/v1/manage/events/{$event->id}/ticket-types/{$ticketType->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id]);
    }

    // --- Discount codes ---------------------------------------------------

    public function test_a_manager_can_update_a_discount_code(): void
    {
        $manager = $this->manager();
        $code = DiscountCode::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $response = $this->actingAs($manager, 'sanctum')->patchJson("/api/v1/manage/discount-codes/{$code->id}", [
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
        ]);

        $response->assertOk()->assertJsonPath('discount_code.code', 'SAVE20');
    }

    public function test_a_discount_code_can_keep_its_own_code_when_updated(): void
    {
        $manager = $this->manager();
        $code = DiscountCode::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $response = $this->actingAs($manager, 'sanctum')->patchJson("/api/v1/manage/discount-codes/{$code->id}", [
            'code' => 'SAVE10',
            'type' => 'fixed',
            'value' => 5,
        ]);

        $response->assertOk()->assertJsonPath('discount_code.type', 'fixed');
    }

    public function test_a_manager_can_delete_a_discount_code(): void
    {
        $manager = $this->manager();
        $code = DiscountCode::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $response = $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/manage/discount-codes/{$code->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('discount_codes', ['id' => $code->id]);
    }

    // --- Promoters ---------------------------------------------------------

    public function test_a_manager_can_update_a_promoter(): void
    {
        $manager = $this->manager();
        $promoter = Promoter::create(['name' => 'Old Name', 'commission_rate' => 10]);

        $response = $this->actingAs($manager, 'sanctum')->patchJson("/api/v1/manage/promoters/{$promoter->id}", [
            'name' => 'New Name',
            'commission_rate' => 15,
        ]);

        $response->assertOk()->assertJsonPath('promoter.name', 'New Name');
    }

    public function test_a_manager_can_delete_a_promoter(): void
    {
        $manager = $this->manager();
        $promoter = Promoter::create(['name' => 'Old Name', 'commission_rate' => 10]);
        [$event] = $this->eventWithTicketType();
        $event->update(['promoter_id' => $promoter->id]);

        $response = $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/manage/promoters/{$promoter->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('promoters', ['id' => $promoter->id]);
        // events.promoter_id is "on delete set null" — the event survives as in-house.
        $this->assertNull($event->fresh()->promoter_id);
    }
}
