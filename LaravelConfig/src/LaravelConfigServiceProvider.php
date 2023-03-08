<?php

namespace MouYong\LaravelConfig;

use Illuminate\Support\ServiceProvider;

class LaravelConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-config.php', 'laravel-config');
        $this->loadMigrationsFrom(__DIR__.'/../migrations/');

        $this->publishes([
            __DIR__.'/../config/laravel-config.php' => config_path('laravel-config.php'),
        ], 'laravel-config-config');

        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations'),
        ], 'laravel-config-migrations');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}