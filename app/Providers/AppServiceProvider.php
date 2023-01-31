<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Debugbar::disable();
        Http::macro('cryptopayment', function () {
            return Http::baseUrl('https://cryptopaymentapi.net');
        });

        Http::macro('affiliates', function () {
            return Http::baseUrl('https://affiliates.bitfiring.com/');
        });
    }
}
