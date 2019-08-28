<?php

namespace Hollyit\LaravelLock;

use Illuminate\Support\ServiceProvider;

class LaravelLockServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-lock.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../migrations/2019_08_28_162752_create_lock_table.php' => database_path('migrations/'.date('Y_m_d_His',
                        time()).'_create_locks_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'lock');

        // Register the main class to use with the facade
        $this->app->singleton(Lock::class, function () {

            return new Lock($this->app, $this->app['config']['lock']);
        });
        $this->app->alias(Lock::class, 'laravel_lock');
    }
}
