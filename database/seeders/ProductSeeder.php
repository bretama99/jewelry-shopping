<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\MetalCategory;
use App\Models\Subcategory;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Product::truncate();
        // Get metal categories and subcategories
        $goldCategory = MetalCategory::where('symbol', 'XAU')->first();
        $silverCategory = MetalCategory::where('symbol', 'XAG')->first();
        
        $ringsSubcategory = Subcategory::where('slug', 'rings')->first();
        $earringsSubcategory = Subcategory::where('slug', 'earrings')->first();
        $necklacesSubcategory = Subcategory::where('slug', 'necklaces')->first();
        $braceletsSubcategory = Subcategory::where('slug', 'bracelets')->first();

        $products = [
            // Gold Rings
            [
                'name' => 'Classic Gold Wedding Ring 18K',
                'slug' => 'classic-gold-wedding-ring-18k',
                'description' => 'Sophisticated Classic Gold Wedding Ring 18K in premium 18K gold. Our skilled artisans have created this exceptional ring with meticulous attention to quality and style.',
                'metal_category_id' => $goldCategory?->id,
                'subcategory_id' => $ringsSubcategory?->id,
                'karat' => '18',
                'weight' => 3.5,
                'min_weight' => 1.75,
                'max_weight' => 10.5,
                'weight_step' => 0.1,
                'labor_cost' => 150.00,
                'profit_margin' => 25.00,
                'stock_quantity' => 10,
                'min_stock_level' => 2,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
                'image' => null,
                'gallery' => [],
                'tags' => ['wedding', 'classic', 'gold', '18k'],
                'meta_title' => 'Classic Gold Wedding Ring 18K - Premium Quality',
                'meta_description' => 'Sophisticated 18K gold wedding ring crafted by skilled artisans. Perfect for your special day with exceptional quality and timeless style.',
            ],
            [
                'name' => 'Elegant Diamond Engagement Ring 14K',
                'slug' => 'elegant-diamond-engagement-ring-14k',
                'description' => 'Beautiful engagement ring featuring a brilliant diamond in premium 14K gold setting. Perfect for proposing to your loved one.',
                'metal_category_id' => $goldCategory?->id,
                'subcategory_id' => $ringsSubcategory?->id,
                'karat' => '14',
                'weight' => 4.2,
                'min_weight' => 2.1,
                'max_weight' => 8.4,
                'weight_step' => 0.1,
                'labor_cost' => 200.00,
                'profit_margin' => 30.00,
                'stock_quantity' => 5,
                'min_stock_level' => 1,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'image' => null,
                'gallery' => [],
                'tags' => ['engagement', 'diamond', 'gold', '14k', 'elegant'],
                'meta_title' => 'Elegant Diamond Engagement Ring 14K Gold',
                'meta_description' => 'Beautiful 14K gold engagement ring with brilliant diamond. Perfect for proposals with exceptional craftsmanship and timeless elegance.',
            ],

            // Silver Rings
            [
                'name' => 'Sterling Silver Band Ring',
                'slug' => 'sterling-silver-band-ring',
                'description' => 'Simple and elegant sterling silver band ring. Perfect for everyday wear or as a statement piece.',
                'metal_category_id' => $silverCategory?->id,
                'subcategory_id' => $ringsSubcategory?->id,
                'karat' => '925',
                'weight' => 2.8,
                'min_weight' => 1.4,
                'max_weight' => 8.4,
                'weight_step' => 0.1,
                'labor_cost' => 75.00,
                'profit_margin' => 20.00,
                'stock_quantity' => 15,
                'min_stock_level' => 3,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
                'image' => null,
                'gallery' => [],
                'tags' => ['silver', '925', 'band', 'everyday', 'simple'],
                'meta_title' => 'Sterling Silver Band Ring - Simple Elegance',
                'meta_description' => 'Elegant sterling silver band ring perfect for everyday wear. Quality 925 silver craftsmanship at an affordable price.',
            ],

            // Gold Earrings
            [
                'name' => 'Gold Drop Earrings 18K',
                'slug' => 'gold-drop-earrings-18k',
                'description' => 'Stunning 18K gold drop earrings that add elegance to any outfit. Lightweight and comfortable for all-day wear.',
                'metal_category_id' => $goldCategory?->id,
                'subcategory_id' => $earringsSubcategory?->id,
                'karat' => '18',
                'weight' => 2.1,
                'min_weight' => 1.0,
                'max_weight' => 6.3,
                'weight_step' => 0.05,
                'labor_cost' => 120.00,
                'profit_margin' => 25.00,
                'stock_quantity' => 8,
                'min_stock_level' => 2,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'image' => null,
                'gallery' => [],
                'tags' => ['earrings', 'drop', 'gold', '18k', 'elegant'],
                'meta_title' => 'Gold Drop Earrings 18K - Elegant Design',
                'meta_description' => 'Stunning 18K gold drop earrings. Lightweight, comfortable, and perfect for adding elegance to any outfit.',
            ],

            // Silver Earrings
            [
                'name' => 'Silver Stud Earrings 925',
                'slug' => 'silver-stud-earrings-925',
                'description' => 'Classic sterling silver stud earrings. Perfect for everyday wear and professional settings.',
                'metal_category_id' => $silverCategory?->id,
                'subcategory_id' => $earringsSubcategory?->id,
                'karat' => '925',
                'weight' => 1.2,
                'min_weight' => 0.6,
                'max_weight' => 3.6,
                'weight_step' => 0.05,
                'labor_cost' => 50.00,
                'profit_margin' => 20.00,
                'stock_quantity' => 20,
                'min_stock_level' => 5,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'image' => null,
                'gallery' => [],
                'tags' => ['earrings', 'stud', 'silver', '925', 'classic', 'everyday'],
                'meta_title' => 'Silver Stud Earrings 925 - Classic Style',
                'meta_description' => 'Classic sterling silver stud earrings perfect for everyday wear and professional settings. Quality 925 silver.',
            ],

            // Gold Necklaces
            [
                'name' => 'Gold Chain Necklace 14K',
                'slug' => 'gold-chain-necklace-14k',
                'description' => 'Beautiful 14K gold chain necklace. Versatile piece that can be worn alone or with pendants.',
                'metal_category_id' => $goldCategory?->id,
                'subcategory_id' => $necklacesSubcategory?->id,
                'karat' => '14',
                'weight' => 8.5,
                'min_weight' => 4.25,
                'max_weight' => 25.5,
                'weight_step' => 0.2,
                'labor_cost' => 180.00,
                'profit_margin' => 28.00,
                'stock_quantity' => 6,
                'min_stock_level' => 1,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
                'image' => null,
                'gallery' => [],
                'tags' => ['necklace', 'chain', 'gold', '14k', 'versatile'],
                'meta_title' => 'Gold Chain Necklace 14K - Versatile Beauty',
                'meta_description' => 'Beautiful 14K gold chain necklace. Versatile piece perfect for layering or wearing alone. Premium quality craftsmanship.',
            ],

            // Silver Necklaces
            [
                'name' => 'Sterling Silver Pendant Necklace',
                'slug' => 'sterling-silver-pendant-necklace',
                'description' => 'Elegant sterling silver necklace with beautiful pendant design. Perfect statement piece for any occasion.',
                'metal_category_id' => $silverCategory?->id,
                'subcategory_id' => $necklacesSubcategory?->id,
                'karat' => '925',
                'weight' => 5.2,
                'min_weight' => 2.6,
                'max_weight' => 15.6,
                'weight_step' => 0.2,
                'labor_cost' => 90.00,
                'profit_margin' => 22.00,
                'stock_quantity' => 12,
                'min_stock_level' => 3,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
                'image' => null,
                'gallery' => [],
                'tags' => ['necklace', 'pendant', 'silver', '925', 'statement'],
                'meta_title' => 'Sterling Silver Pendant Necklace - Statement Piece',
                'meta_description' => 'Elegant sterling silver pendant necklace. Perfect statement piece for any occasion with beautiful design.',
            ],

            // Gold Bracelets
            [
                'name' => 'Gold Tennis Bracelet 18K',
                'slug' => 'gold-tennis-bracelet-18k',
                'description' => 'Luxurious 18K gold tennis bracelet with brilliant finish. Perfect for special occasions and elegant events.',
                'metal_category_id' => $goldCategory?->id,
                'subcategory_id' => $braceletsSubcategory?->id,
                'karat' => '18',
                'weight' => 12.3,
                'min_weight' => 6.15,
                'max_weight' => 36.9,
                'weight_step' => 0.3,
                'labor_cost' => 220.00,
                'profit_margin' => 30.00,
                'stock_quantity' => 3,
                'min_stock_level' => 1,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 7,
                'image' => null,
                'gallery' => [],
                'tags' => ['bracelet', 'tennis', 'gold', '18k', 'luxury', 'elegant'],
                'meta_title' => 'Gold Tennis Bracelet 18K - Luxury Design',
                'meta_description' => 'Luxurious 18K gold tennis bracelet with brilliant finish. Perfect for special occasions and elegant events.',
            ],

            // Silver Bracelets
            [
                'name' => 'Sterling Silver Charm Bracelet',
                'slug' => 'sterling-silver-charm-bracelet',
                'description' => 'Beautiful sterling silver charm bracelet. Add your favorite charms to create a personalized piece.',
                'metal_category_id' => $silverCategory?->id,
                'subcategory_id' => $braceletsSubcategory?->id,
                'karat' => '925',
                'weight' => 6.8,
                'min_weight' => 3.4,
                'max_weight' => 20.4,
                'weight_step' => 0.2,
                'labor_cost' => 85.00,
                'profit_margin' => 20.00,
                'stock_quantity' => 10,
                'min_stock_level' => 2,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
                'image' => null,
                'gallery' => [],
                'tags' => ['bracelet', 'charm', 'silver', '925', 'personalized'],
                'meta_title' => 'Sterling Silver Charm Bracelet - Personalized Style',
                'meta_description' => 'Beautiful sterling silver charm bracelet. Add your favorite charms to create a unique personalized piece.',
            ],
        ];

        foreach ($products as $productData) {
            // Generate unique SKU if not provided
            if (!isset($productData['sku'])) {
                do {
                    $sku = 'PRD-' . strtoupper(Str::random(8));
                } while (Product::where('sku', $sku)->exists());
                
                $productData['sku'] = $sku;
            }

            Product::updateOrCreate(
    ['slug' => $productData['slug']], // Find by slug
    $productData // Update or create with this data
);
        }

        $this->command->info('Products seeded successfully!');
    }
}