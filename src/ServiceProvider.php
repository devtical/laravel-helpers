<?php

namespace Kristories\Helpers;

use Kristories\Helpers\Console\Commands\HelperMakeCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/helpers.php';

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

}
