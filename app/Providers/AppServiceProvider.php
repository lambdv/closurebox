<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Cashier\Cashier;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        // RateLimiter::for('create-product', function ($job) {
        //     return Limit::perMinute(1)->by($job->orginization->id);
        // });
        //Model::preventLazyLoading();
        Cashier::calculateTaxes();
        Cashier::useCustomerModel(User::class);
    }
}
