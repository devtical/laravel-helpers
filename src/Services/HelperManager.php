<?php

namespace Devtical\Helpers\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class HelperManager
{
    /**
     * @var list<string>
     */
    protected array $loadedFiles = [];

    /**
     * @var array<string, Throwable>
     */
    protected array $failedFiles = [];

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
        } catch (Throwable $exception) {
            $this->recordLoadFailure($file, $exception);
        }
    }

    protected function recordLoadFailure(string $file, Throwable $exception): void
    {
        $this->failedFiles[$file] = $exception;

        if (config('helpers.log_errors', true)) {
            Log::warning('Failed to load helper file.', [
                'file' => $file,
                'message' => $exception->getMessage(),
            ]);
        }

        if (config('helpers.strict', false)) {
            throw $exception;
        }
    }

    /**
     * @return list<string>
     */
    public function getLoadedFiles(): array
    {
        return $this->loadedFiles;
    }

    /**
     * @return array<string, Throwable>
     */
    public function getFailedFiles(): array
    {
        return $this->failedFiles;
    }

    public function hasFailures(): bool
    {
        return $this->failedFiles !== [];
    }

    public function isLoaded(string $file): bool
    {
        return in_array($file, $this->loadedFiles, true);
    }

    public function isFailed(string $file): bool
    {
        return array_key_exists($file, $this->failedFiles);
    }

    public function reload(): void
    {
        $this->loadedFiles = [];
        $this->failedFiles = [];
        $this->loadHelpers();
    }
}
