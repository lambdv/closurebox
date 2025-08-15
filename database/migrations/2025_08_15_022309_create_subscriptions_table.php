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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'stripe_status']);
        });

        /**
         * // subscriptions table (base subscription)
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('stripe_id')->nullable()->unique();
    $table->string('stripe_price_id'); // Base $5/month price
    $table->string('status');
    $table->timestamp('current_period_start');
    $table->timestamp('current_period_end');
    $table->timestamps();
});

// usage_records table (track consumption)
Schema::create('usage_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('subscription_id')->constrained();
    $table->string('stripe_usage_record_id')->nullable();
    $table->string('product_type'); // 'database_queries', 'storage_gb', etc.
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 4);
    $table->timestamp('timestamp');
    $table->boolean('billed')->default(false);
    $table->timestamps();
});

// invoices table (combined billing)
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('stripe_invoice_id')->unique();
    $table->decimal('base_amount', 10, 2); // $5 base fee
    $table->decimal('usage_amount', 10, 2); // Usage charges
    $table->decimal('total_amount', 10, 2);
    $table->string('status');
    $table->timestamp('period_start');
    $table->timestamp('period_end');
    $table->timestamps();
});
         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
