<?php

namespace App\Providers;

use App\Repositories\Contracts\CashFlowRepositoryInterface;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\SimpleProductRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Eloquent\CashFlowRepository;
use App\Repositories\Eloquent\MenuItemRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\SaleRepository;
use App\Repositories\Eloquent\SimpleProductRepository;
use App\Repositories\Eloquent\TableRepository;
use App\Repositories\Interfaces\CashRegisterRepositoryInterface;
use App\Repositories\CashRegisterRepository;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Observers\ProductObserver;
use App\Observers\SaleObserver;
use App\Observers\SaleReturnObserver;
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
        $this->app->bind(CashRegisterRepositoryInterface::class, CashRegisterRepository::class);
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Establecer timezone dinámicamente desde BusinessSettings
        try {
            $settings = \App\Models\BusinessSettings::get();
            $timezone = $settings->timezone ?? 'America/El_Salvador';

            // Establecer timezone para Laravel (afecta Carbon y Eloquent timestamps)
            config(['app.timezone' => $timezone]);

            // Establecer timezone para PHP (afecta date(), time(), etc.)
            date_default_timezone_set($timezone);
        } catch (\Exception $e) {
            // Si falla (ej: durante migraciones o cuando no existe la tabla aún)
            // usar timezone por defecto
            config(['app.timezone' => 'America/El_Salvador']);
            date_default_timezone_set('America/El_Salvador');
        }

        Product::observe(ProductObserver::class);
        Sale::observe(SaleObserver::class);
        SaleReturn::observe(SaleReturnObserver::class);
    }
}
