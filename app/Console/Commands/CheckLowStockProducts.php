<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckLowStockProducts extends Command
{
    protected $signature = 'products:check-low-stock';

    protected $description = 'Check for products with low stock and send notifications';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $lowStockProducts = Product::whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('No products with low stock found.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($lowStockProducts as $product) {
            $this->notificationService->lowStock(
                $product->name,
                (int) $product->current_stock,
                (int) $product->min_stock
            );
            $count++;
        }

        $this->info("Sent {$count} low stock notifications.");

        return Command::SUCCESS;
    }
}
