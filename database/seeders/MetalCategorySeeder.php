<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetalCategory;

class MetalCategorySeeder extends Seeder
{
    public function run(): void
    {
        $metalCategories = [
            [
                'name' => 'Gold',
                'symbol' => 'XAU',
                'description' => 'Precious yellow metal, symbol of wealth and luxury',
                'current_price_usd' => 2000.00,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Silver',
                'symbol' => 'XAG',
                'description' => 'Precious white metal, affordable luxury option',
                'current_price_usd' => 25.00,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Platinum',
                'symbol' => 'XPT',
                'description' => 'Rare precious metal, extremely durable',
                'current_price_usd' => 1000.00,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Palladium',
                'symbol' => 'XPD',
                'description' => 'Modern precious metal, popular in contemporary jewelry',
                'current_price_usd' => 1500.00,
                'is_active' => true,
                'sort_order' => 4
            ]
        ];

        foreach ($metalCategories as $metalData) {
            MetalCategory::updateOrCreate(
                ['symbol' => $metalData['symbol']],
                $metalData
            );
        }

        $this->command->info('Metal categories seeded successfully!');
    }
}