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
        if (!Schema::hasTable('import_logs')) {
            Schema::create('import_logs', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->integer('rows_processed')->default(0);
                $table->integer('failures')->default(0);
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('export_logs')) {
            Schema::create('export_logs', function (Blueprint $table) {
                $table->id();
                $table->string('format');
                $table->json('filters')->nullable();
                $table->string('status')->default('pending');
                $table->string('download_link')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('scheduled_tasks')) {
            Schema::create('scheduled_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('task_name');
                $table->timestamp('last_run_at')->nullable();
                $table->string('status')->default('success');
                $table->text('output')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('api_rate_limits')) {
            Schema::create('api_rate_limits', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('endpoint');
                $table->integer('hits')->default(1);
                $table->boolean('throttled')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('backup_monitoring')) {
            Schema::create('backup_monitoring', function (Blueprint $table) {
                $table->id();
                $table->string('type'); 
                $table->string('status'); 
                $table->string('disk')->nullable();
                $table->integer('size_kb')->nullable();
                $table->string('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_monitoring');
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('scheduled_tasks');
        Schema::dropIfExists('export_logs');
        Schema::dropIfExists('import_logs');
    }
};
