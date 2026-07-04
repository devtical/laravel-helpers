<?php

use Devtical\Helpers\Services\HelperManager;

it('creates helper file using make:helper command', function () {
    $path = app_path('Helpers/TestHelper.php');

    $this->artisan('make:helper', [
        'name' => 'TestHelper',
        '--force' => true,
    ])->assertSuccessful();

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('function testHelper(');
    expect($content)->toContain('declare(strict_types=1);');
});

it('creates helper file in subdirectory', function () {
    $path = app_path('Helpers/Utils/StringHelper.php');

    $this->artisan('make:helper', [
        'name' => 'utils/StringHelper',
        '--force' => true,
    ])->assertSuccessful();

    expect(file_exists($path))->toBeTrue();
});

it('loads created helper files through helper manager', function () {
    $this->artisan('make:helper', [
        'name' => 'RuntimeHelper',
        '--force' => true,
    ])->assertSuccessful();

    $manager = app(HelperManager::class);
    $manager->reload();

    expect($manager->getLoadedFiles())->not->toBeEmpty();
    expect(function_exists('runtimeHelper'))->toBeTrue();
});

it('reloads helper files via artisan command', function () {
    writeHelperFile('ReloadCommandHelper.php', <<<'PHP'
        <?php
        if (! function_exists('reloadCommandHelper')) {
            function reloadCommandHelper() {
                return true;
            }
        }
        PHP);

    $this->artisan('helper:reload')
        ->expectsOutputToContain('Reloaded 1 helper file(s).')
        ->assertSuccessful();
});

it('has correct package structure and files', function () {
    expect(file_exists(__DIR__.'/../../src/HelperServiceProvider.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Services/HelperManager.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/HelperMakeCommand.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/HelperListCommand.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/HelperValidateCommand.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../src/Console/Commands/HelperReloadCommand.php'))->toBeTrue();
    expect(file_exists(__DIR__.'/../../config/helpers.php'))->toBeTrue();

    $config = include __DIR__.'/../../config/helpers.php';
    expect($config)->toBeArray();
    expect($config)->toHaveKeys(['directory', 'log_errors', 'strict']);
});
