<?php

namespace App\Providers;

use App\Console\Commands\NotifyTasksDueSoon;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\NotifyTasksDueSoon::class, // Đảm bảo path đúng
            ]);
        }
    }
}
