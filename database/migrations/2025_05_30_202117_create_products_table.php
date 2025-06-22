<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
Schema::create('products', function (Blueprint $table) {
    $table->id();

    $table->foreignId('metal_category_id')->constrained()->onDelete('cascade');
    $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');

    // Add SKU
    $table->string('sku')->unique();

    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->integer('sort_order')->default(0);

    $table->decimal('weight', 8, 3)->default(1.000);
    $table->decimal('min_weight', 8, 3)->default(0.500);
    $table->decimal('max_weight', 8, 3)->default(10.000);
    $table->decimal('weight_step', 8, 3)->default(0.100);

    $table->string('karat', 10)->default('18');

    $table->decimal('labor_cost', 8, 2)->nullable();
    $table->decimal('profit_margin', 5, 2)->nullable();

    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();

    $table->timestamps();
            $table->softDeletes();

    $table->index(['metal_category_id', 'subcategory_id', 'is_active']);
});

    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
