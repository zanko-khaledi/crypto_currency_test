<?php

namespace App\Providers;

use App\Services\Currencies\CurrencyService;
use App\Services\Currencies\Drivers\CurrencyServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->bind(CurrencyServiceInterface::class,function (){
             return app(config('currency.drivers.default'));
         });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
