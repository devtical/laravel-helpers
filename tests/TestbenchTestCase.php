<?php

namespace Devtical\Helpers\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestbenchTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set facade root for testing
        \Illuminate\Support\Facades\Facade::setFacadeApplication($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Devtical\Helpers\HelperServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup helpers config
        $app['config']->set('helpers', [
            'directory' => 'Helpers',
        ]);
    }
}
