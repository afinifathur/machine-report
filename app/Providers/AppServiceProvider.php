<?php

namespace App\Providers;

use App\Repositories\WarehouseRepositoryInterface;
use App\Repositories\MockWarehouseRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WarehouseRepositoryInterface::class, MockWarehouseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::component('layouts.app', 'layouts.app');
    }
}
