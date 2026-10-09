<?php

namespace App\Providers;

use App\Models\Transactions;
use App\Observers\TransactionNotificationObserver;
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
        Transactions::observe(TransactionNotificationObserver::class);
    }
}
