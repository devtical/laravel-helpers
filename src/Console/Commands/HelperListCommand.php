<?php

namespace Devtical\Helpers\Console\Commands;

use Devtical\Helpers\Services\HelperManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class HelperListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helper:list 
                            {--loaded : Show only loaded helper files}
                            {--details : Show detailed information about each file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available helper files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $helperManager = app()->make(HelperManager::class);
        $directory = app_path(config('helpers.directory', 'Helpers'));

        if (! File::exists($directory)) {
            $this->warn("Helper directory does not exist: {$directory}");
            $this->info('Run "php artisan make:helper example" to create your first helper file.');

            return 0;
        }

        $files = $this->getHelperFiles($directory);

        if (empty($files)) {
            $this->warn('No helper files found.');
            $this->info('Run "php artisan make:helper example" to create your first helper file.');

            return 0;
        }

        $this->info('Found '.count($files).' helper file(s):');
        $this->newLine();

        $headers = ['File', 'Status'];
        $rows = [];

        foreach ($files as $file) {
            // Get relative path from helpers directory
            $helperDir = app_path(config('helpers.directory', 'Helpers'));
            $relativePath = str_replace($helperDir.'/', '', $file);
            $filename = $relativePath;

            $status = $helperManager->isLoaded($file) ? 'Loaded' : 'Not Loaded';

            if ($this->option('loaded') && $status !== 'Loaded') {
                continue;
            }

            $rows[] = [$filename, $status];
        }

        // Sort rows by filename
        usort($rows, function ($a, $b) {
            return strcmp($a[0], $b[0]);
        });

        $this->table($headers, $rows);

        if ($this->option('details')) {
            $this->showDetails($files, $helperManager);
        }

        return 0;
    }

    /**
     * Get all helper files from the directory (including subdirectories).
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
     * Show detailed information about helper files.
     *
     * @param  array  $files
     * @param  HelperManager  $helperManager
     * @return void
     */
    protected function showDetails($files, $helperManager)
    {
        $this->newLine();
        $this->info('Detailed Information:');
        $this->newLine();

        foreach ($files as $file) {
            // Get relative path from helpers directory
            $helperDir = app_path(config('helpers.directory', 'Helpers'));
            $relativePath = str_replace($helperDir.'/', '', $file);

            $this->line("<fg=cyan>{$relativePath}</>");
            $this->line('  Status: '.($helperManager->isLoaded($file) ? 'Loaded' : 'Not Loaded'));

            // Try to extract function names from the file
            $content = File::get($file);
            $functions = $this->extractFunctionNames($content);

            if (! empty($functions)) {
                $this->line('  Functions: '.implode(', ', $functions));
            }

            $this->newLine();
        }
    }

    /**
     * Extract function names from file content.
     *
     * @param  string  $content
     * @return array
     */
    protected function extractFunctionNames($content)
    {
        preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches);

        return $matches[1] ?? [];
    }
}
