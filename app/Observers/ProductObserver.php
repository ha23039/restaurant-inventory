<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\NotificationService;

class ProductObserver
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function updated(Product $product): void
    {
        if ($product->isDirty('current_stock')) {
            if ($product->current_stock <= $product->min_stock && $product->current_stock > 0) {
                $this->notificationService->lowStock(
                    $product->name,
                    (int) $product->current_stock,
                    (int) $product->min_stock
                );
            }
        }
    }
}
