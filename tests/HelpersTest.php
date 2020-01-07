<?php

namespace Kristories\Helpers\Tests;

use Kristories\Helpers\Facades\Helpers;
use Kristories\Helpers\ServiceProvider;
use Orchestra\Testbench\TestCase;

class HelpersTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'helpers' => Helpers::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }
}
