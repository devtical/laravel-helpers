<?php

namespace Devtical\Helpers\Tests;

class HelpersTest extends TestCase
{
    /**
     * Test a console command.
     *
     * @return void
     */
    public function test_console_command()
    {
        $this->artisan('make:helper', ['name' => 'TestHelper'])->assertExitCode(0);
    }
}
