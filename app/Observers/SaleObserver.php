<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\NotificationService;

class SaleObserver
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function created(Sale $sale): void
    {
        if ($sale->status === 'completed') {
            $this->notificationService->saleCompleted(
                $sale->sale_number,
                $sale->total_amount
            );
        }
    }

    public function updated(Sale $sale): void
    {
        if ($sale->isDirty('status') && $sale->status === 'completed') {
            $this->notificationService->saleCompleted(
                $sale->sale_number,
                $sale->total_amount
            );
        }
    }
}
