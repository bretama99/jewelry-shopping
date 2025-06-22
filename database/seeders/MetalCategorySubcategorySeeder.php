<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetalCategory;
use App\Models\Subcategory;

class MetalCategorySubcategorySeeder extends Seeder
{
    public function run()
    {
        // Get all metal categories and subcategories
        $metalCategories = MetalCategory::all();
        $subcategories = Subcategory::all();

        // Create many-to-many relationships - each metal can be used for each jewelry type
        foreach ($metalCategories as $metalCategory) {
            foreach ($subcategories as $subcategory) {
                $metalCategory->subcategories()->attach($subcategory->id, [
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
