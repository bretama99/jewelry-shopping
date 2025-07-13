<?php
// File: database/migrations/2024_01_01_000004_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ORDER-2024-001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Customer Information
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();

            // Order Details
            $table->decimal('subtotal', 12, 2); // Sum of all items
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); // Final total
            $table->string('currency', 3)->default('AUD');

            // Status & Tracking
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped',
                'delivered', 'cancelled', 'refunded'
            ])->default('pending');
            $table->string('payment_status')->default('pending'); // pending, paid, failed
            $table->string('payment_method')->nullable(); // cash, card, bank_transfer
            $table->string('tracking_number')->nullable();

            // Gold Market Info (for record keeping)
            $table->decimal('gold_price_at_order', 10, 2); // Gold price when order placed
            $table->json('market_data')->nullable(); // Store market conditions

            // Special Instructions
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['customer_email', 'created_at']);
            $table->index('order_number');
                        $table->softDeletes(); // Adds deleted_at column

        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
