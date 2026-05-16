<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_archives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('archive_file');
            $table->integer('records_count');
            $table->date('cutoff_date');
            $table->timestamp('archived_at');
            $table->timestamps();
            
            $table->index('archived_at');
            $table->index('cutoff_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_archives');
    }
};
