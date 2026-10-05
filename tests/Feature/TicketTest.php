<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful creation of a ticket via API.
     */
    public function test_can_create_ticket_via_api(): void
    {
        $payload = [
            'title' => 'Server issue',
            'requester_name' => 'Ali Rezai',
            'priority' => 'high',
            'description' => 'Server response time is high.',
        ];

        $response = $this->postJson('/api/tickets', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Server issue')
            ->assertJsonPath('data.status', 'open');

        $this->assertDatabaseHas('tickets', [
            'title' => 'Server issue',
            'requester_name' => 'Ali Rezai',
            'status' => 'open',
        ]);
    }

    /**
     * Test validation error when required fields are missing.
     */
    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/tickets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'requester_name', 'priority']);
    }

    /**
     * Test updating ticket status and tracking history.
     */
    public function test_ticket_status_update_and_history_logging(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'last_opened_at' => now()->subHours(2),
        ]);

        $response = $this->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'from_status' => 'open',
            'to_status' => 'in_progress',
        ]);
    }

    /**
     * Test overdue filter correctly detects tickets exceeding SLA.
     */
    public function test_overdue_scope_filters_tickets_past_24_hours(): void
    {
        // Ticket 1: High priority, open for 25 hours (OVERDUE)
        Ticket::factory()->create([
            'priority' => 'high',
            'status' => 'open',
            'last_opened_at' => now()->subHours(25),
            'total_open_seconds' => 0,
        ]);

        // Ticket 2: High priority, open for only 2 hours (NOT OVERDUE)
        Ticket::factory()->create([
            'priority' => 'high',
            'status' => 'open',
            'last_opened_at' => now()->subHours(2),
            'total_open_seconds' => 0,
        ]);

        // Ticket 3: Low priority, open for 30 hours (NOT OVERDUE because priority is not high)
        Ticket::factory()->create([
            'priority' => 'low',
            'status' => 'open',
            'last_opened_at' => now()->subHours(30),
            'total_open_seconds' => 0,
        ]);

        // Query API with is_overdue=1
        $response = $this->getJson('/api/tickets?is_overdue=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }
}
