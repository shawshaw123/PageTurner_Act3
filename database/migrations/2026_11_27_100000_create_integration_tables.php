<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables and columns for the integration points in Figure 4.1:
 *  - payments, webhook_events  (IP-2 / IP-3 PayMongo)
 *  - notification_logs, jobs   (IP-4 email through a queue worker)
 *  - users.google_id           (IP-6 Google sign-in)
 *  - orders.payment_status     (paid / failed / expired shown on the order)
 *  - books.isbn_lookup_used, books.listing_seconds (IP-1, to measure listing time)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('checkout_reference', 40)->unique(); // the orders this payment covers
            $table->string('provider', 20)->default('paymongo');
            $table->string('checkout_session_id')->nullable()->unique(); // cs_...
            $table->string('payment_intent_id')->nullable()->index();    // pi_...
            $table->string('provider_payment_id')->nullable();           // pay_...
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('status', 20)->default('pending'); // pending | paid | failed | expired
            $table->text('checkout_url')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 20);
            $table->string('event_id')->nullable()->unique(); // evt_... ; makes processing idempotent
            $table->string('type')->nullable();
            $table->boolean('signature_valid');
            $table->string('status', 20); // processed | ignored | rejected | failed
            $table->longText('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification', 100);
            $table->string('channel', 20);
            $table->string('recipient')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20); // sent | failed
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('delivery_seconds')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
        });

        Schema::table('orders', function (Blueprint $table) {
            // unpaid (cash on delivery) | awaiting_payment | paid | failed | expired
            $table->string('payment_status', 20)->default('unpaid')->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->boolean('isbn_lookup_used')->default(false)->after('cover_image');
            $table->unsignedInteger('listing_seconds')->nullable()->after('isbn_lookup_used');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['isbn_lookup_used', 'listing_seconds']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn('google_id');
        });
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');
        // jobs is left in place: other parts of the app may queue work.
    }
};
