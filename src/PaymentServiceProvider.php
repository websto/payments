<?php

namespace Websto\Payments;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */

    public function boot()
    {
        $source = realpath(__DIR__.'/../config/paymentconf.php');

        $this->publishes([$source => config_path('paymentconf.php')]);

        $this->loadRoutesFrom(__DIR__.'/../src/Http/routes.php');

        // php artisan vendor:publish

        //$this->publishes([__DIR__ . '/../resources/css/app_.css' => public_path('css/app_.css'),]);

        //$this->publishes([__DIR__ . '/../resources/js/app_.js' => public_path('js/app_.js'),]);


        $this->loadViewsFrom(__DIR__.'/../resources/views', 'payment-block');

        $this->mergeConfigFrom($source, 'paymentconf');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }


}