<?php

namespace App\Providers;

use App\Repositories\Contracts\CashFlowRepositoryInterface;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\SimpleProductRepositoryInterface;
use App\Repositories\Eloquent\CashFlowRepository;
use App\Repositories\Eloquent\MenuItemRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\SaleRepository;
use App\Repositories\Eloquent\SimpleProductRepository;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository Pattern Bindings
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(CashFlowRepositoryInterface::class, CashFlowRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(SimpleProductRepositoryInterface::class, SimpleProductRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
