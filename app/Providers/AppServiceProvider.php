<?php

namespace App\Providers;

use App\Http\Requests\CustomValidation;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        CustomValidation::init();
        view()->composer('frontend.components.navbar', 'App\Http\Controllers\Frontend\PartialsController@navbar');
        view()->composer('frontend.components.footer', 'App\Http\Controllers\Frontend\PartialsController@footer');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
