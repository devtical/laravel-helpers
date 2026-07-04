<?php

use Devtical\Helpers\Services\HelperManager;
use Illuminate\Support\Facades\File;

it('loads valid helper files', function () {
    writeHelperFile('ValidHelper.php', <<<'PHP'
        <?php
        if (! function_exists('validHelper')) {
            function validHelper() {
                return true;
            }
        }
        PHP);

    $manager = app(HelperManager::class);
    $manager->reload();

    expect($manager->getLoadedFiles())->toHaveCount(1);
    expect($manager->hasFailures())->toBeFalse();
    expect(function_exists('validHelper'))->toBeTrue();
});

it('records failed helper files without strict mode', function () {
    config(['helpers.log_errors' => false, 'helpers.strict' => false]);

    writeHelperFile('BrokenLoadHelper.php', <<<'PHP'
        <?php
        function brokenLoadHelper( {
        }
        PHP);

    $manager = app(HelperManager::class);
    $manager->reload();

    expect($manager->hasFailures())->toBeTrue();
    expect($manager->getFailedFiles())->not->toBeEmpty();
});

it('throws when strict mode is enabled and a helper fails to load', function () {
    config(['helpers.log_errors' => false, 'helpers.strict' => true]);

    writeHelperFile('StrictBrokenHelper.php', <<<'PHP'
        <?php
        function strictBrokenHelper( {
        }
        PHP);

    $manager = app(HelperManager::class);

    expect(fn () => $manager->reload())->toThrow(ParseError::class);
});

it('reloads helper files', function () {
    writeHelperFile('ReloadHelper.php', <<<'PHP'
        <?php
        if (! function_exists('reloadHelper')) {
            function reloadHelper() {
                return 'first';
            }
        }
        PHP);

    $manager = app(HelperManager::class);
    $manager->reload();

    expect($manager->getLoadedFiles())->toHaveCount(1);

    writeHelperFile('AnotherHelper.php', <<<'PHP'
        <?php
        if (! function_exists('anotherHelper')) {
            function anotherHelper() {
                return true;
            }
        }
        PHP);

    $manager->reload();

    expect($manager->getLoadedFiles())->toHaveCount(2);
});

it('discovers helper files recursively', function () {
    $path = helperTestDirectory().'/Utils';
    File::makeDirectory($path, 0755, true);
    File::put($path.'/NestedHelper.php', '<?php');

    $manager = app(HelperManager::class);

    expect($manager->discoverHelperFiles())->toHaveCount(1);
});
