<?php

namespace App\Providers;

use App\Contracts\RzdDocumentServiceInterface;
use App\Services\RzdDocumentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RzdDocumentServiceInterface::class, RzdDocumentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
