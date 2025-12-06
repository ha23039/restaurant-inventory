<?php

namespace App\Observers;

use App\Models\SaleReturn;
use App\Services\NotificationService;

class SaleReturnObserver
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function created(SaleReturn $saleReturn): void
    {
        $this->notificationService->returnProcessed(
            $saleReturn->return_number,
            $saleReturn->total_amount
        );
    }
}
