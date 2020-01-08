<?php

namespace Kristories\Helpers;

use Kristories\Helpers\Console\Commands\HelperMakeCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__.'/../config/helpers.php';

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
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'helper'
        );

        $files = glob(
            app_path(config('helpers.directory', 'Helpers').'/*.php')
        );

        foreach ($files as $file) {
            require_once $file;
        }
    }
}
