<?php

use Devtical\Helpers\Services\HelperManager;

it('lists helper files with function counts', function () {
    writeHelperFile('ListHelper.php', <<<'PHP'
        <?php
        if (! function_exists('listHelper')) {
            function listHelper() {
                return true;
            }
        }
        if (! function_exists('listHelperTwo')) {
            function listHelperTwo() {
                return true;
            }
        }
        PHP);

    app(HelperManager::class)->reload();

    $this->artisan('helper:list')
        ->expectsOutputToContain('ListHelper.php')
        ->assertSuccessful();
});

it('outputs helper list as json', function () {
    writeHelperFile('JsonHelper.php', <<<'PHP'
        <?php
        if (! function_exists('jsonHelper')) {
            function jsonHelper() {
                return true;
            }
        }
        PHP);

    app(HelperManager::class)->reload();

    $this->artisan('helper:list', ['--json' => true])
        ->expectsOutputToContain('"file": "JsonHelper.php"')
        ->assertSuccessful();
});

it('shows failed status for broken helper files', function () {
    config(['helpers.log_errors' => false, 'helpers.strict' => false]);

    writeHelperFile('FailedHelper.php', <<<'PHP'
        <?php
        if (! function_exists('failedHelper')) {
            function failedHelper( {
        }
        PHP);

    app(HelperManager::class)->reload();

    $this->artisan('helper:list')
        ->expectsOutputToContain('Failed')
        ->assertSuccessful();
});
