<?php

namespace Devtical\Helpers\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class HelperManager
{
    /**
     * @var list<string>
     */
    protected array $loadedFiles = [];

    public function __construct(protected Application $app) {}

    /**
     * Load all helper files from the configured directory.
     */
    public function loadHelpers(): void
    {
        $directory = $this->getHelperDirectoryPath();

        if (! File::exists($directory)) {
            $this->createHelperDirectory($directory);

            return;
        }

        foreach ($this->collectPhpFilesInDirectory($directory) as $file) {
            $this->loadHelperFile($file);
        }
    }

    /**
     * Absolute path to the configured helpers directory (under app_path).
     */
    public function getHelperDirectoryPath(): string
    {
        $directory = config('helpers.directory', 'Helpers');

        return app_path($directory);
    }

    /**
     * All .php files under the helpers directory (recursive), or empty if missing.
     *
     * @return list<string>
     */
    public function discoverHelperFiles(): array
    {
        $directory = $this->getHelperDirectoryPath();

        if (! File::exists($directory)) {
            return [];
        }

        return $this->collectPhpFilesInDirectory($directory);
    }

    /**
     * Path relative to the helpers directory, using forward slashes for display.
     */
    public function getRelativeHelperPath(string $absoluteFilePath): string
    {
        $dir = str_replace('\\', '/', rtrim($this->getHelperDirectoryPath(), '/\\'));
        $file = str_replace('\\', '/', $absoluteFilePath);

        if (str_starts_with($file, $dir.'/')) {
            return substr($file, strlen($dir) + 1);
        }

        return $file;
    }

    /**
     * Create the helper directory if it doesn't exist.
     */
    protected function createHelperDirectory(string $directory): void
    {
        File::makeDirectory($directory, 0755, true);
    }

    /**
     * @return list<string>
     */
    protected function collectPhpFilesInDirectory(string $directory): array
    {
        $files = [];

        if (! File::exists($directory)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Load a single helper file.
     */
    protected function loadHelperFile(string $file): void
    {
        if (in_array($file, $this->loadedFiles, true)) {
            return;
        }

        try {
            require_once $file;
            $this->loadedFiles[] = $file;
        } catch (\ParseError) {
            return;
        } catch (\Error) {
            return;
        } catch (\Exception) {
            return;
        }
    }

    /**
     * @return list<string>
     */
    public function getLoadedFiles(): array
    {
        return $this->loadedFiles;
    }

    public function isLoaded(string $file): bool
    {
        return in_array($file, $this->loadedFiles, true);
    }

    public function reload(): void
    {
        $this->loadedFiles = [];
        $this->loadHelpers();
    }
}
