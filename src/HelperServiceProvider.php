<?php

namespace Devtical\Helpers;

use Devtical\Helpers\Console\Commands\HelperListCommand;
use Devtical\Helpers\Console\Commands\HelperMakeCommand;
use Devtical\Helpers\Services\HelperManager;
use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public const CONFIG_PATH = __DIR__.'/../config/helpers.php';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('helpers.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                HelperMakeCommand::class,
                HelperListCommand::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'helpers');

        $this->app->singleton(HelperManager::class, function ($app) {
            return new HelperManager($app);
        });

        $this->loadHelpers();
    }

    /**
     * Load all helper files from the configured directory.
     *
     * @return void
     */
    protected function loadHelpers()
    {
        $helperManager = $this->app->make(HelperManager::class);
        $helperManager->loadHelpers();
    }
}
