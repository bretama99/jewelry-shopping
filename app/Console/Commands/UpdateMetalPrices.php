<?php
// File: app/Console/Commands/UpdateMetalPrices.php
namespace App\Console\Commands;

use App\Models\MetalCategory;
use App\Services\MetalPriceApiService;
use Illuminate\Console\Command;

class UpdateMetalPrices extends Command
{
    protected $signature = 'metals:update-prices {--force : Force update even if recently updated}';
    protected $description = 'Update metal prices from external API';

    protected $metalPriceService;

    public function __construct(MetalPriceApiService $metalPriceService)
    {
        parent::__construct();
        $this->metalPriceService = $metalPriceService;
    }

    public function handle()
    {
        $this->info('Starting metal price update...');

        $metalCategories = MetalCategory::where('is_active', true)->get();
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($metalCategories as $category) {
            $this->line("Updating {$category->name} ({$category->symbol})...");

            try {
                $result = $this->metalPriceService->updatePrice($category->symbol, $this->option('force'));

                if ($result['success']) {
                    $this->info("✓ {$category->symbol}: ${result['price']} USD/oz");
                    $updatedCount++;
                } else {
                    $this->warn("⚠ {$category->symbol}: {$result['error']}");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->error("✗ {$category->symbol}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Price update completed:");
        $this->info("- Updated: {$updatedCount}");
        $this->info("- Errors: {$errorCount}");

        return $errorCount === 0 ? 0 : 1;
    }
}
