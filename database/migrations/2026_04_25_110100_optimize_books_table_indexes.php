<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes individually with existence checks to avoid duplicate key errors
        $this->addIndexIfNotExists('books', ['category_id', 'published_at', 'is_active'], 'idx_books_catalog_filter');
        $this->addIndexIfNotExists('books', ['price', 'stock_quantity', 'id'], 'idx_books_price_stock');
        $this->addFullTextIfNotExists('books', ['title', 'description'], 'idx_books_fulltext');
        $this->addIndexIfNotExists('books', ['is_active'], 'idx_books_active');
        $this->addIndexIfNotExists('books', ['isbn'], 'idx_books_isbn_lookup');
    }

    private function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'"))->isNotEmpty();
        
        if (!$exists) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    private function addFullTextIfNotExists(string $table, array $columns, string $indexName): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'"))->isNotEmpty();
        
        if (!$exists) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->fullText($columns, $indexName);
            });
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('idx_books_active');
            $table->dropIndex('idx_books_isbn_lookup');
            $table->dropIndex('idx_books_catalog_filter');
            $table->dropIndex('idx_books_price_stock');
            $table->dropIndex('idx_books_fulltext');
        });
    }
};
