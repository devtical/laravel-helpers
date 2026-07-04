<?php

use Devtical\Helpers\Tests\TestbenchTestCase;
use Illuminate\Support\Facades\File;

uses(TestbenchTestCase::class)->in('Feature', 'Unit');

function helperTestDirectory(): string
{
    return app_path('Helpers');
}

function writeHelperFile(string $filename, string $content): string
{
    $directory = helperTestDirectory();

    if (! File::exists($directory)) {
        File::makeDirectory($directory, 0755, true);
    }

    $path = $directory.'/'.$filename;
    File::put($path, $content);

    return $path;
}
