<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['open', 'in_progress', 'done']);
        $createdAt = fake()->dateTimeBetween('-5 days', 'now');

        return [
            'title' => fake()->sentence(4),
            'requester_name' => fake()->name(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => $status,
            'total_open_seconds' => $status === 'open' ? 0 : fake()->numberBetween(600, 7200),
            'last_opened_at' => $status === 'open' ? $createdAt : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * State for generating overdue tickets (high priority, open > 24 hours).
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'status' => 'open',
            'total_open_seconds' => 90000, // > 24 hours
            'last_opened_at' => now()->subHours(26),
            'created_at' => now()->subDays(2),
        ]);
    }
}
