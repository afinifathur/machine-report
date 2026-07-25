<?php

namespace App\Providers;

use App\Integrations\WMS\Repositories\SparepartLookupRepositoryInterface;
use App\Integrations\WMS\Repositories\DatabaseSparepartLookupRepository;
use App\Repositories\WarehouseRepositoryInterface;
use App\Repositories\WarehouseRepositoryAdapter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SparepartLookupRepositoryInterface::class, DatabaseSparepartLookupRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepositoryAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::component('layouts.app', 'layouts.app');
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\ProcurementAttachment::class,
            \App\Policies\ProcurementCasePolicy::class
        );
    }
}
