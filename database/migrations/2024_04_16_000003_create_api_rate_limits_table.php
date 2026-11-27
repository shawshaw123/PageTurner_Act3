<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier'); // IP address or user ID
            $table->string('identifier_type'); // 'ip' or 'user'
            $table->string('endpoint')->nullable();
            $table->string('tier'); // public, standard, premium, admin, auth
            $table->integer('requests_count');
            $table->integer('limit');
            $table->timestamp('window_start')->nullable();
            $table->timestamp('window_end')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['identifier', 'identifier_type', 'window_start']);
            $table->index('tier');
            $table->index('endpoint');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_rate_limits');
    }
};
