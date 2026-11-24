<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Route::model('table', \App\Models\RestaurantTable::class);
        \Illuminate\Support\Facades\Route::model('call', \App\Models\QueueCall::class);
    }
}
