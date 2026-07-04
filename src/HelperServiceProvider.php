<?php

namespace Devtical\Helpers;

use Devtical\Helpers\Console\Commands\HelperListCommand;
use Devtical\Helpers\Console\Commands\HelperMakeCommand;
use Devtical\Helpers\Console\Commands\HelperReloadCommand;
use Devtical\Helpers\Console\Commands\HelperValidateCommand;
use Devtical\Helpers\Services\HelperManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HelperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-helpers')
            ->hasCommands([
                HelperMakeCommand::class,
                HelperListCommand::class,
                HelperValidateCommand::class,
                HelperReloadCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/helpers.php', 'helpers');

        $this->app->singleton(HelperManager::class, function ($app) {
            return new HelperManager($app);
        });

        $this->app->make(HelperManager::class)->loadHelpers();
    }

    public function packageBooted(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/helpers.php' => config_path('helpers.php'),
            ], 'helpers-config');
        }
    }
}
