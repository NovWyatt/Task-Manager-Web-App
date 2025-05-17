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
        //migrate long
        Schema::defaultStringLength(191);
        //notify
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\NotifyTasksDueSoon::class, // Đảm bảo path đúng
            ]);
        }
        //render 
        if ($this->app->environment('production')) {
            if (isset($_ENV['DATABASE_URL'])) {
                $url = parse_url($_ENV['DATABASE_URL']);
                $host = $url["host"];
                $username = $url["user"];
                $password = $url["pass"];
                $database = substr($url["path"], 1);
                $port = $url["port"] ?? 5432;

                config(['database.connections.pgsql.host' => $host]);
                config(['database.connections.pgsql.port' => $port]);
                config(['database.connections.pgsql.database' => $database]);
                config(['database.connections.pgsql.username' => $username]);
                config(['database.connections.pgsql.password' => $password]);
            }
        }
    }
}
