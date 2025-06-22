<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add stock management columns
            $table->integer('stock_quantity')->default(0)->after('profit_margin');
            $table->integer('min_stock_level')->default(1)->after('stock_quantity');
            
            // Add JSON columns for gallery and tags
            $table->json('gallery')->nullable()->after('image');
            $table->json('tags')->nullable()->after('meta_description');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'min_stock_level', 'gallery', 'tags']);
        });
    }
};