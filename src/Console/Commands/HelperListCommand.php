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
     */
    public function handle(): int
    {
        $helperManager = app()->make(HelperManager::class);
        $directory = $helperManager->getHelperDirectoryPath();

        if (! File::exists($directory)) {
            $this->warn("Helper directory does not exist: {$directory}");
            $this->info('Run "php artisan make:helper example" to create your first helper file.');

            return 0;
        }

        $files = $helperManager->discoverHelperFiles();

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
            $filename = $helperManager->getRelativeHelperPath($file);
            $status = $helperManager->isLoaded($file) ? 'Loaded' : 'Not Loaded';

            if ($this->option('loaded') && $status !== 'Loaded') {
                continue;
            }

            $rows[] = [$filename, $status];
        }

        usort($rows, fn ($a, $b) => strcmp($a[0], $b[0]));

        $this->table($headers, $rows);

        if ($this->option('details')) {
            $this->showDetails($files, $helperManager);
        }

        return 0;
    }

    /**
     * @param  list<string>  $files
     */
    protected function showDetails(array $files, HelperManager $helperManager): void
    {
        $this->newLine();
        $this->info('Detailed Information:');
        $this->newLine();

        foreach ($files as $file) {
            $relativePath = $helperManager->getRelativeHelperPath($file);

            $this->line("<fg=cyan>{$relativePath}</>");
            $this->line('  Status: '.($helperManager->isLoaded($file) ? 'Loaded' : 'Not Loaded'));

            $content = File::get($file);
            $functions = $this->extractFunctionNames($content);

            if (! empty($functions)) {
                $this->line('  Functions: '.implode(', ', $functions));
            }

            $this->newLine();
        }
    }

    /**
     * @return list<string>
     */
    protected function extractFunctionNames(string $content): array
    {
        preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches);

        return $matches[1] ?? [];
    }
}
