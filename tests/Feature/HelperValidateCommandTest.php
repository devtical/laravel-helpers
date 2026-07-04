<?php

it('validates helper files successfully', function () {
    writeHelperFile('ValidHelper.php', <<<'PHP'
        <?php
        if (! function_exists('validHelper')) {
            function validHelper() {
                return true;
            }
        }
        PHP);

    $this->artisan('helper:validate')
        ->expectsOutputToContain('All 1 helper file(s) are valid.')
        ->assertSuccessful();
});

it('reports syntax errors during validation', function () {
    writeHelperFile('BrokenHelper.php', <<<'PHP'
        <?php
        if (! function_exists('brokenHelper')) {
            function brokenHelper( {
        }
        PHP);

    $this->artisan('helper:validate')
        ->expectsOutputToContain('Syntax errors found:')
        ->assertFailed();
});

it('reports duplicate function names during validation', function () {
    writeHelperFile('FirstHelper.php', <<<'PHP'
        <?php
        if (! function_exists('duplicateHelper')) {
            function duplicateHelper() {
                return 1;
            }
        }
        PHP);

    writeHelperFile('SecondHelper.php', <<<'PHP'
        <?php
        if (! function_exists('duplicateHelper')) {
            function duplicateHelper() {
                return 2;
            }
        }
        PHP);

    $this->artisan('helper:validate')
        ->expectsOutputToContain('Duplicate function names found:')
        ->assertFailed();
});
