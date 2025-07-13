<?php
// Create this migration file: database/migrations/xxxx_xx_xx_create_metal_categories_table.php

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
        Schema::create('metal_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 10)->nullable(); // XAU, XAG, XPT, XPD
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('current_price_usd', 10, 2)->default(0);
            $table->decimal('aud_rate', 6, 4)->default(1.45);
            $table->json('purity_ratios')->nullable(); // Store karat/purity ratios
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metal_categories');
    }
};
