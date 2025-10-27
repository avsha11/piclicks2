<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Repository\FrontEnd\Cart\CartRepositoryInterface;
use App\Repository\FrontEnd\Cart\CartRepository;
use Binafy\LaravelCart\LaravelCart;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
        public function register(): void
{
    $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
    
    // Bind LaravelCart to 'binafy-cart'
    $this->app->singleton('binafy-cart', function ($app) {
        return new LaravelCart();
    });
}


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(191);
    }
}
