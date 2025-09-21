<?php

use Devtical\Helpers\Services\HelperManager;
use Devtical\Helpers\Tests\TestbenchTestCase;

uses(TestbenchTestCase::class);

it('has correct package structure and files', function () {
    // Check essential files exist
    expect(file_exists(__DIR__.'/../../src/HelperServiceProvider.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Services/HelperManager.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/HelperMakeCommand.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/HelperListCommand.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../config/helpers.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/stubs/helper.stub'))->toBeTrue();

    // Check config file is valid
    $config = include __DIR__.'/../../config/helpers.php';
    expect($config)->toBeArray();
    expect($config)->toHaveKey('directory');

    // Check stub file has required placeholders
    $stubContent = file_get_contents(__DIR__.'/../../src/Console/Commands/stubs/helper.stub');
    expect($stubContent)->toContain('{{functionName}}');
    expect($stubContent)->toContain('{{description}}');
    expect($stubContent)->toContain('{{author}}');
    expect($stubContent)->toContain('{{date}}');
});

it('creates actual helper file using make:helper command', function () {
    $dir = app_path('Helpers');
    $path = $dir.'/TestHelper.php';

    if (! file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    if (file_exists($path)) {
        unlink($path);
    }

    $this->artisan('make:helper', [
        'name' => 'TestHelper',
        '--force' => true,
    ])->run();

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('function testHelper(');

    unlink($path);
});

it('can instantiate HelperManager with required methods', function () {
    $manager = new HelperManager($this->app);

    expect($manager)->toBeInstanceOf(HelperManager::class);
    expect(method_exists($manager, 'loadHelpers'))->toBeTrue();
    expect(method_exists($manager, 'isLoaded'))->toBeTrue();
    expect(method_exists($manager, 'getLoadedFiles'))->toBeTrue();
});
