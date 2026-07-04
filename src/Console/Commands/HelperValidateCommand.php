<?php

namespace Devtical\Helpers\Console\Commands;

use Devtical\Helpers\Concerns\InteractsWithHelperFiles;
use Devtical\Helpers\Services\HelperManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class HelperValidateCommand extends Command
{
    use InteractsWithHelperFiles;

    /**
     * @var string
     */
    protected $signature = 'helper:validate';

    /**
     * @var string
     */
    protected $description = 'Validate helper files for syntax errors and duplicate functions';

    public function handle(): int
    {
        $helperManager = app()->make(HelperManager::class);
        $directory = $helperManager->getHelperDirectoryPath();

        if (! File::exists($directory)) {
            $this->warn("Helper directory does not exist: {$directory}");

            return self::SUCCESS;
        }

        $files = $helperManager->discoverHelperFiles();

        if ($files === []) {
            $this->warn('No helper files found.');

            return self::SUCCESS;
        }

        $hasErrors = false;
        $syntaxErrors = [];

        foreach ($files as $file) {
            $result = $this->validatePhpSyntax($file);

            if (! $result['valid']) {
                $hasErrors = true;
                $syntaxErrors[] = [
                    $helperManager->getRelativeHelperPath($file),
                    $result['message'] ?? 'Unknown syntax error',
                ];
            }
        }

        if ($syntaxErrors !== []) {
            $this->error('Syntax errors found:');
            $this->table(['File', 'Error'], $syntaxErrors);
        }

        $duplicates = $this->findDuplicateFunctions($files);

        if ($duplicates !== []) {
            $hasErrors = true;
            $this->newLine();
            $this->error('Duplicate function names found:');

            $rows = [];

            foreach ($duplicates as $functionName => $paths) {
                $relativePaths = array_map(
                    fn (string $path) => $helperManager->getRelativeHelperPath($path),
                    $paths
                );

                $rows[] = [$functionName, implode(', ', $relativePaths)];
            }

            $this->table(['Function', 'Files'], $rows);
        }

        if ($hasErrors) {
            return self::FAILURE;
        }

        $this->info('All '.count($files).' helper file(s) are valid.');

        return self::SUCCESS;
    }
}
