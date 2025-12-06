<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringProducts extends Command
{
    protected $signature = 'products:check-expiring {--days=7 : Number of days to check for expiring products}';

    protected $description = 'Check for products expiring soon and send notifications';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $expiryDate = Carbon::now()->addDays($days);

        $expiringProducts = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $expiryDate)
            ->where('expiry_date', '>=', Carbon::now())
            ->where('current_stock', '>', 0)
            ->get();

        if ($expiringProducts->isEmpty()) {
            $this->info("No products expiring in the next {$days} days.");
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($expiringProducts as $product) {
            $this->notificationService->productExpiringSoon(
                $product->name,
                $product->expiry_date->format('d/m/Y')
            );
            $count++;
        }

        $this->info("Sent {$count} expiring product notifications.");

        return Command::SUCCESS;
    }
}
