<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subcategory;

class SubcategorySeeder extends Seeder
{
    public function run()
    {
        $subcategories = [
            [
                'name' => 'Rings',
                'slug' => 'rings',
                'description' => 'Beautiful rings for every occasion',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Necklaces',
                'slug' => 'necklaces',
                'description' => 'Elegant necklaces and chains',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Bracelets',
                'slug' => 'bracelets',
                'description' => 'Stylish bracelets and bangles',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Earrings',
                'slug' => 'earrings',
                'description' => 'Beautiful earrings collection',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Chains',
                'slug' => 'chains',
                'description' => 'Premium chains and links',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Pendants',
                'slug' => 'pendants',
                'description' => 'Beautiful pendants and charms',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::updateOrCreate(
                ['slug' => $subcategory['slug']], // Find by slug
                $subcategory // Update or create with this data
            );
        }

        $this->command->info('Subcategories seeded successfully!');
    }
}