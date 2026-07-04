<?php

namespace Devtical\Helpers\Console\Commands;

use Devtical\Helpers\Services\HelperManager;
use Illuminate\Console\Command;

class HelperReloadCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'helper:reload';

    /**
     * @var string
     */
    protected $description = 'Reload all helper files';

    public function handle(): int
    {
        $helperManager = app()->make(HelperManager::class);

        $helperManager->reload();

        $loaded = count($helperManager->getLoadedFiles());
        $failed = count($helperManager->getFailedFiles());

        $this->info("Reloaded {$loaded} helper file(s).");

        if ($failed > 0) {
            $this->warn("{$failed} helper file(s) failed to load.");

            foreach ($helperManager->getFailedFiles() as $file => $exception) {
                $this->line("  - {$file}: {$exception->getMessage()}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
