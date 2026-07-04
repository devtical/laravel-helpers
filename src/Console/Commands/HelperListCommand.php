<?php

namespace Devtical\Helpers\Console\Commands;

use Devtical\Helpers\Concerns\InteractsWithHelperFiles;
use Devtical\Helpers\Services\HelperManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class HelperListCommand extends Command
{
    use InteractsWithHelperFiles;

    /**
     * @var string
     */
    protected $signature = 'helper:list
                            {--loaded : Show only loaded helper files}
                            {--details : Show detailed information about each file}
                            {--json : Output the list as JSON}';

    /**
     * @var string
     */
    protected $description = 'List all available helper files';

    public function handle(): int
    {
        $helperManager = app()->make(HelperManager::class);
        $directory = $helperManager->getHelperDirectoryPath();

        if (! File::exists($directory)) {
            $this->warn("Helper directory does not exist: {$directory}");
            $this->info('Run "php artisan make:helper example" to create your first helper file.');

            return self::SUCCESS;
        }

        $files = $helperManager->discoverHelperFiles();

        if ($files === []) {
            $this->warn('No helper files found.');
            $this->info('Run "php artisan make:helper example" to create your first helper file.');

            return self::SUCCESS;
        }

        $items = $this->buildListItems($files, $helperManager);

        if ($this->option('json')) {
            $this->line(json_encode($items, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->info('Found '.count($files).' helper file(s):');
        $this->newLine();

        $rows = array_map(fn (array $item) => [
            $item['file'],
            (string) $item['functions'],
            $item['status'],
        ], $items);

        $this->table(['File', 'Functions', 'Status'], $rows);

        if ($this->option('details')) {
            $this->showDetails($items);
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $files
     * @return list<array{file: string, functions: int, status: string, function_names: list<string>, error: string|null}>
     */
    protected function buildListItems(array $files, HelperManager $helperManager): array
    {
        $items = [];

        foreach ($files as $file) {
            $relativePath = $helperManager->getRelativeHelperPath($file);
            $content = (string) File::get($file);
            $functionNames = $this->extractFunctionNames($content);
            $status = $this->resolveStatus($file, $helperManager);
            $error = $helperManager->isFailed($file)
                ? $helperManager->getFailedFiles()[$file]->getMessage()
                : null;

            if ($this->option('loaded') && $status !== 'Loaded') {
                continue;
            }

            $items[] = [
                'file' => $relativePath,
                'functions' => count($functionNames),
                'status' => $status,
                'function_names' => $functionNames,
                'error' => $error,
            ];
        }

        usort($items, fn (array $a, array $b) => strcmp($a['file'], $b['file']));

        return $items;
    }

    protected function resolveStatus(string $file, HelperManager $helperManager): string
    {
        if ($helperManager->isFailed($file)) {
            return 'Failed';
        }

        if ($helperManager->isLoaded($file)) {
            return 'Loaded';
        }

        return 'Not Loaded';
    }

    /**
     * @param  list<array{file: string, functions: int, status: string, function_names: list<string>, error: string|null}>  $items
     */
    protected function showDetails(array $items): void
    {
        $this->newLine();
        $this->info('Detailed Information:');
        $this->newLine();

        foreach ($items as $item) {
            $this->line("<fg=cyan>{$item['file']}</>");
            $this->line('  Status: '.$item['status']);

            if ($item['error'] !== null) {
                $this->line('  Error: '.$item['error']);
            }

            if ($item['function_names'] !== []) {
                $this->line('  Functions: '.implode(', ', $item['function_names']));
            }

            $this->newLine();
        }
    }
}
