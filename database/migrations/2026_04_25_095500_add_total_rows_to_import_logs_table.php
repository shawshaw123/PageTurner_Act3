<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('import_logs', 'total_rows')) {
                $table->integer('total_rows')->default(0)->after('original_filename');
            }
            if (!Schema::hasColumn('import_logs', 'processed_rows')) {
                $table->integer('processed_rows')->default(0)->after('total_rows');
            }
            if (!Schema::hasColumn('import_logs', 'successful_rows')) {
                $table->integer('successful_rows')->default(0)->after('processed_rows');
            }
            if (!Schema::hasColumn('import_logs', 'failed_rows')) {
                $table->integer('failed_rows')->default(0)->after('successful_rows');
            }
        });
    }

    public function down(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropColumn(['total_rows', 'processed_rows', 'successful_rows', 'failed_rows']);
        });
    }
};
