<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 20 normal tickets
        Ticket::factory()->count(20)->create();

        // 5 explicit overdue tickets to verify SLA logic easily
        Ticket::factory()->overdue()->count(5)->create();
    }
}
