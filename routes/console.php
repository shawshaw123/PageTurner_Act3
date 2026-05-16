<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Schedule::command('app:refresh-materialized-views')
    ->hourly()
    ->withoutOverlapping();

Artisan::command('app:refresh-materialized-views', function () {
    $this->info('Refreshing materialized views...');
    
    // Call the logic from the migration or a dedicated service
    // For simplicity, we can invoke the migration's logic or a raw SQL here
    // But better to have it in a service. 
    // Since I already implemented it in the migration class as a public method, 
    // I will just execute the SQL here directly for the command.
    
    DB::transaction(function () {
        // Refresh Bestseller Stats
        DB::table('mv_bestseller_stats')->truncate();
        DB::statement("
            INSERT INTO mv_bestseller_stats 
            SELECT 
                b.id, b.title, b.author, b.publisher, b.price, b.stock_quantity, 
                b.category_id, c.name, b.published_at,
                (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.id),
                COALESCE((SELECT AVG(rating) FROM reviews r WHERE r.book_id = b.id), 0),
                (b.stock_quantity * 1.0 / (SELECT MAX(stock_quantity) FROM books)),
                CASE 
                    WHEN b.stock_quantity > 100 AND b.published_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 'Bestseller'
                    WHEN b.stock_quantity > 50 AND b.published_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) THEN 'Popular'
                    ELSE 'Regular'
                END,
                NOW()
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.is_active = true
        ");

        // Refresh Inventory Summary
        DB::table('mv_inventory_summary')->truncate();
        DB::statement("
            INSERT INTO mv_inventory_summary
            SELECT 
                c.id, c.name, COUNT(b.id), SUM(COALESCE(b.stock_quantity, 0)), 
                AVG(b.price), MIN(b.price), MAX(b.price),
                SUM(CASE WHEN b.stock_quantity > 0 THEN 1 ELSE 0 END),
                SUM(CASE WHEN b.stock_quantity = 0 THEN 1 ELSE 0 END),
                ROUND((SUM(CASE WHEN b.stock_quantity > 0 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(b.id), 0)), 2),
                NOW()
            FROM categories c
            LEFT JOIN books b ON c.id = b.category_id AND b.is_active = true
            GROUP BY c.id, c.name
        ");
    });
    
    $this->info('Materialized views refreshed successfully.');
})->purpose('Refresh reporting tables (materialized views)');
