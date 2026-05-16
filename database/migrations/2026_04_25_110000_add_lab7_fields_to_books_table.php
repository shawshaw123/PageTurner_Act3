<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('publisher')->nullable()->after('author');
            $table->string('format')->default('Paperback')->after('price');
            $table->boolean('is_active')->default(true)->after('stock_quantity');
            $table->date('published_at')->nullable()->after('is_active');
            $table->integer('pages')->nullable()->after('published_at');
            $table->string('language')->default('English')->after('pages');
            $table->string('dimensions')->nullable()->after('language');
            $table->decimal('weight', 5, 2)->nullable()->after('dimensions');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'publisher',
                'format',
                'is_active',
                'published_at',
                'pages',
                'language',
                'dimensions',
                'weight'
            ]);
        });
    }
};
