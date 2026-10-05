<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('requester_name');
            $table->text('description')->nullable();
            
            // Priority and status according to specifications
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'done'])->default('open');
            
            // SLA tracking fields for performant overdue calculation
            $table->unsignedBigInteger('total_open_seconds')->default(0);
            $table->timestamp('last_opened_at')->nullable();

            $table->timestamps();

            // Indexes for fast searching and filtering
            $table->index('status');
            $table->index('priority');
            $table->index(['title', 'requester_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
