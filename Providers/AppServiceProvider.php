<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DomainServiceInterface;
use App\Contracts\StaticDomainInterface;
use App\Contracts\PagesInterface;
use App\Services\DomainService;
use App\Services\StaticDnsService;
use App\Services\PagesService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DomainServiceInterface::class, DomainService::class);
        $this->app->bind(StaticDomainInterface::class, StaticDnsService::class);
        $this->app->bind(PagesInterface::class, PagesService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
