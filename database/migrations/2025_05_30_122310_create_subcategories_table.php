<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Rings, Necklaces, etc.
            // $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('default_labor_cost', 8, 2)->default(15.00); // Default labor cost per gram
            $table->boolean('is_active')->default(true);
            // $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            // $table->string('meta_title')->nullable();
            // $table->text('meta_description')->nullable();
            $table->timestamps();
                        $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('subcategories');
    }
};
