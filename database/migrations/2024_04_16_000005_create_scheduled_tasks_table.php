<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('command'); // Artisan command name
            $table->string('description')->nullable();
            $table->string('schedule'); // Cron expression
            $table->enum('status', ['active', 'inactive', 'running', 'failed'])->default('active');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['command', 'status']);
            $table->index('next_run_at');
            $table->index('last_run_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
