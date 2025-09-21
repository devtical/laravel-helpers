<?php

namespace Devtical\Helpers\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;

class HelperManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * The loaded helper files.
     *
     * @var array
     */
    protected $loadedFiles = [];

    /**
     * Create a new HelperManager instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Load all helper files from the configured directory.
     *
     * @return void
     */
    public function loadHelpers()
    {
        $directory = $this->getHelperDirectory();

        if (! File::exists($directory)) {
            $this->createHelperDirectory($directory);

            return;
        }

        $files = $this->getHelperFiles($directory);

        foreach ($files as $file) {
            $this->loadHelperFile($file);
        }
    }

    /**
     * Get the helper directory path.
     *
     * @return string
     */
    protected function getHelperDirectory()
    {
        $directory = config('helpers.directory', 'Helpers');

        return app_path($directory);
    }

    /**
     * Create the helper directory if it doesn't exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function createHelperDirectory($directory)
    {
        File::makeDirectory($directory, 0755, true);
    }

    /**
     * Get all PHP files from the helper directory (including subdirectories).
     *
     * @param  string  $directory
     * @return array
     */
    protected function getHelperFiles($directory)
    {
        $files = [];

        if (! File::exists($directory)) {
            return $files;
        }

        // Use recursive directory iterator for better subdirectory support
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
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
     *
     * @param  string  $file
     * @return void
     */
    protected function loadHelperFile($file)
    {
        if (in_array($file, $this->loadedFiles)) {
            return;
        }

        try {
            require_once $file;
            $this->loadedFiles[] = $file;
        } catch (\ParseError $e) {
            // Skip files with parse errors
            return;
        } catch (\Error $e) {
            // Skip files with fatal errors
            return;
        } catch (\Exception $e) {
            // Skip files with exceptions
            return;
        }
    }

    /**
     * Get all loaded helper files.
     *
     * @return array
     */
    public function getLoadedFiles()
    {
        return $this->loadedFiles;
    }

    /**
     * Check if a helper file is loaded.
     *
     * @param  string  $file
     * @return bool
     */
    public function isLoaded($file)
    {
        return in_array($file, $this->loadedFiles);
    }

    /**
     * Reload all helper files.
     *
     * @return void
     */
    public function reload()
    {
        $this->loadedFiles = [];
        $this->loadHelpers();
    }
}
