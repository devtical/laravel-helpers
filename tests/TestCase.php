<?php

namespace Devtical\Helpers\Tests;

use Devtical\Helpers\HelperServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            HelperServiceProvider::class,
        ];
    }
}
