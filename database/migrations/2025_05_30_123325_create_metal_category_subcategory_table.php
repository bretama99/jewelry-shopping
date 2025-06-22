<?php
// Create this migration file: database/migrations/xxxx_xx_xx_create_metal_category_subcategory_table.php

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
        Schema::create('metal_category_subcategory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metal_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $table->decimal('labor_cost_override', 8, 2)->nullable();
            $table->decimal('profit_margin_override', 5, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // $table->unique(['metal_category_id', 'subcategory_id']);
            $table->index(['metal_category_id', 'is_available']);
            $table->index(['subcategory_id', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metal_category_subcategory');
    }
};
