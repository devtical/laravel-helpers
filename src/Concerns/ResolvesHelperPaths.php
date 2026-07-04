<?php

namespace Devtical\Helpers\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait ResolvesHelperPaths
{
    protected function getHelperDirectory(): string
    {
        return app_path(config('helpers.directory', 'Helpers'));
    }

    protected function getHelperFilePath(string $name): string
    {
        $directory = $this->getHelperDirectory();
        $nameParts = preg_split('/[\/\\\\]/', $name);

        if (count($nameParts) > 1) {
            $dirParts = array_slice($nameParts, 0, -1);
            $filename = Str::studly(end($nameParts)).'.php';
            $camelCaseDirParts = array_map(fn ($part) => Str::studly($part), $dirParts);
            $subDir = implode('/', $camelCaseDirParts);

            return $directory.'/'.$subDir.'/'.$filename;
        }

        return $directory.'/'.Str::studly($name).'.php';
    }

    protected function ensureHelperDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info("Created directory: {$directory}");
        }
    }

    protected function convertToCamelCase(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9\s\-_\/]/', '', $name);
        $nameParts = preg_split('/[\/\\\\]/', $name);

        if (count($nameParts) > 1) {
            $camelCaseParts = array_map(function ($part) {
                $part = str_replace(['-', '_'], ' ', $part);

                return Str::studly($part);
            }, $nameParts);

            $name = implode('/', $camelCaseParts);
        } else {
            $name = str_replace(['-', '_'], ' ', $name);
            $name = Str::studly($name);
        }

        if (! $this->hasHelperSuffix($name)) {
            $name = $name.'Helper';
        }

        return $name;
    }

    protected function hasHelperSuffix(string $name): bool
    {
        $filename = basename(str_replace('\\', '/', $name));

        return str_ends_with($filename, 'Helper');
    }

    protected function isValidCamelCase(string $name): bool
    {
        $nameParts = preg_split('/[\/\\\\]/', $name);

        if (count($nameParts) > 1) {
            $filename = end($nameParts);

            return (bool) preg_match('/^[A-Z][a-zA-Z0-9]*$/', $filename);
        }

        return (bool) preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name);
    }

    protected function isValidFunctionName(string $name): bool
    {
        return (bool) preg_match('/^[a-z][a-zA-Z0-9_]*$/', $name);
    }

    protected function resolveDefaultAuthor(): string
    {
        $composerPath = base_path('composer.json');

        if (file_exists($composerPath)) {
            $composer = json_decode((string) file_get_contents($composerPath), true);

            if (! empty($composer['authors'][0]['name'])) {
                return $composer['authors'][0]['name'];
            }
        }

        return (string) config('app.name', 'Laravel');
    }
}
