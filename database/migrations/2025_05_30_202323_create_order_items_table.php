<?php
// File: database/migrations/2024_01_01_000005_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Product Details at Time of Order
            $table->string('product_name'); // Snapshot of product name
            $table->string('product_sku')->nullable();
            $table->string('karat'); // 14K, 18K, 22K, etc.
            $table->string('category_name'); // Snapshot of category
            $table->text('product_description')->nullable();
            $table->string('product_image')->nullable(); // Main image path

            // Pricing & Weight
            $table->decimal('weight', 8, 3); // Weight in grams
            $table->decimal('price_per_gram', 10, 2); // Price per gram at order time
            $table->decimal('gold_price', 10, 2); // Gold price at order time
            $table->decimal('labor_cost', 10, 2)->default(0); // Labor cost per gram
            $table->decimal('profit_margin', 8, 2)->default(0); // Profit margin percentage
            $table->decimal('subtotal', 12, 2); // weight * price_per_gram

            // Additional Details
            $table->json('product_features')->nullable(); // Features snapshot
            $table->json('pricing_breakdown')->nullable(); // Detailed price calculation
            $table->text('special_instructions')->nullable(); // Custom requests

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'product_id']);
            $table->index('karat');
                        $table->softDeletes(); // Adds deleted_at column

        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
