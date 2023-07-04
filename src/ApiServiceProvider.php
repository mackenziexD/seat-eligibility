<?php

namespace busa\seat;

use Seat\Services\AbstractSeatPlugin;


class ApiServiceProvider extends AbstractSeatPlugin
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register any bindings or dependencies here
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Bootstrap sidebar menu items
        $this->mergeConfigFrom(__DIR__.'/Config/package.sidebar.php', 'package.sidebar');


        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'busa-seat');

        // Publish migrations
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/busa-seat'),
        ], 'views');

        // Load package translations
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'busa-seat');
    }

    /**
     * Get the package's routes.
     *
     * @return string
     */
    protected function getRouteFile()
    {
        return __DIR__.'/routes.php';
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     * @example SeAT Web
     *
     */
    public function getName(): string
    {
        return 'SeAT BUSA Test';
    }

    /**
     * Return the plugin repository address.
     *
     * @example https://github.com/eveseat/web
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/mackenziexD/seat-BUSA';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     * @example web
     *
     */
    public function getPackagistPackageName(): string
    {
        return 'busa-seat';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     * @example eveseat
     *
     */
    public function getPackagistVendorName(): string
    {
        return 'busa';
    }
}