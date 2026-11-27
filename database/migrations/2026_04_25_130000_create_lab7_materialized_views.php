<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create materialized view tables for reporting
        Schema::create('mv_bestseller_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->unsignedBigInteger('category_id');
            $table->string('category_name')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('reviews_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->decimal('stock_percentile', 5, 4)->default(0);
            $table->string('popularity_tier')->default('Regular');
            $table->timestamp('last_refreshed_at')->nullable();
            
            $table->index('category_id');
            $table->index('popularity_tier');
        });

        Schema::create('mv_inventory_summary', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->primary();
            $table->string('category_name')->nullable();
            $table->integer('total_books')->default(0);
            $table->integer('total_stock')->default(0);
            $table->decimal('avg_price', 10, 2)->default(0);
            $table->decimal('min_price', 10, 2)->default(0);
            $table->decimal('max_price', 10, 2)->default(0);
            $table->integer('in_stock_books')->default(0);
            $table->integer('out_of_stock_books')->default(0);
            $table->decimal('in_stock_percentage', 5, 2)->default(0);
            $table->timestamp('last_refreshed_at')->nullable();
        });

        // Skip initial population during migration to avoid timeout on 1M records.
        // Use: php artisan app:refresh-materialized-views to populate later.
    }

    public function down(): void
    {
        Schema::dropIfExists('mv_inventory_summary');
        Schema::dropIfExists('mv_bestseller_stats');
    }
};
