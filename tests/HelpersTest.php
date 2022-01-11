<?php

namespace Devtical\Helpers\Tests;

class HelpersTest extends TestCase
{
    /** @test */
    public function test_console_command()
    {
        $this->artisan('make:helper', ['name' => 'TestHelper'])->assertExitCode(0);
    }
}
