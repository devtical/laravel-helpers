<?php

namespace Devtical\Helpers\Tests;

use Devtical\Helpers\HelperServiceProvider;
use Devtical\Helpers\Services\HelperManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

class TestbenchTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Facade::setFacadeApplication($this->app);

        $this->cleanHelperDirectory();
        $this->app->make(HelperManager::class)->reload();
    }

    protected function tearDown(): void
    {
        $this->cleanHelperDirectory();

        parent::tearDown();
    }

    protected function cleanHelperDirectory(): void
    {
        $directory = app_path(config('helpers.directory', 'Helpers'));

        if (File::exists($directory)) {
            File::deleteDirectory($directory);
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            HelperServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('helpers', [
            'directory' => 'Helpers',
            'log_errors' => false,
            'strict' => false,
        ]);
    }
}
