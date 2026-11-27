<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_monitoring', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('backup_type'); // daily, weekly, monthly, manual
            $table->string('status'); // started, completed, failed
            $table->string('disk'); // local, s3, etc.
            $table->string('path')->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('files')->nullable(); // List of backed up files
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['backup_type', 'status']);
            $table->index('started_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_monitoring');
    }
};
