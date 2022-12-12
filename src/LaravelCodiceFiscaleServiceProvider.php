<?php

namespace IvanoMatteo\LaravelCodiceFiscale;

use Illuminate\Support\ServiceProvider;

class LaravelCodiceFiscaleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-codice-fiscale');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-codice-fiscale');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php','laravel-codice-fiscale');

        LaravelCodiceFiscaleFacade::registerValidator();


        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-codice-fiscale.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-codice-fiscale'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-codice-fiscale'),
            ], 'assets');*/

            // Publishing the translation files.
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-codice-fiscale'),
            ], 'lang');

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        //$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-codice-fiscale');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-codice-fiscale', function () {
            return new LaravelCodiceFiscale;
        });
    }
}
