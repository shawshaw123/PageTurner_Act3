<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LEBROn marketplace: sellers, listing ownership and per-seller orders.
 *
 * - users.role becomes a plain string so it can hold admin / seller / customer
 *   (buyers keep the existing "customer" value).
 * - Sellers have a store profile and an approval status set by an admin.
 * - Every book listing belongs to a seller (nullable for legacy store stock).
 * - Every order belongs to one seller; a checkout with books from several
 *   sellers creates one order per seller, linked by checkout_reference.
 * - orders.status becomes a plain string so it can hold "shipped" (and the
 *   payment statuses added later).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->change();
            $table->string('store_name')->nullable()->after('role');
            $table->text('store_description')->nullable()->after('store_name');
            $table->string('seller_status', 20)->nullable()->after('store_description'); // pending | approved | suspended
            $table->index(['role', 'seller_status']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('category_id')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
            $table->foreignId('seller_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
            $table->string('checkout_reference', 40)->nullable()->after('seller_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
            $table->dropIndex(['checkout_reference']);
            $table->dropColumn('checkout_reference');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'seller_status']);
            $table->dropColumn(['store_name', 'store_description', 'seller_status']);
        });
    }
};
