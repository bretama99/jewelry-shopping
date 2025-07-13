<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // For guest users
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // For logged users
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 8, 3); // Weight in grams (e.g., 12.500)
            $table->decimal('price_per_gram', 10, 2); // Price per gram at time of adding
            $table->decimal('gold_price', 10, 2); // Gold price at time of adding
            $table->string('karat'); // Product karat (14K, 18K, etc.)
            $table->decimal('subtotal', 12, 2); // Calculated: weight * price_per_gram
            $table->json('product_snapshot')->nullable(); // Store product details at time of adding
            $table->timestamps();

            // Indexes
            $table->index(['session_id', 'user_id']);
            $table->index('product_id');
                        $table->softDeletes(); // Adds deleted_at column

        });
    }

    public function down()
    {
        Schema::dropIfExists('carts');
    }
};
