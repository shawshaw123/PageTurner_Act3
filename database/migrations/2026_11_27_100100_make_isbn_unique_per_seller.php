<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In a marketplace several sellers can sell the same book, so an ISBN only has
 * to be unique within one seller's listings (the plain ISBN index stays for lookups).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique('books_isbn_unique');
            $table->unique(['seller_id', 'isbn'], 'books_seller_isbn_unique');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique('books_seller_isbn_unique');
            $table->unique('isbn');
        });
    }
};
