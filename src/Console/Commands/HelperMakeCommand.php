<?php

namespace Devtical\Helpers\Console\Commands;

use Devtical\Helpers\Concerns\ResolvesHelperPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HelperMakeCommand extends Command
{
    use ResolvesHelperPaths;

    /**
     * @var string
     */
    protected $signature = 'make:helper
                            {name : The name of the helper file (supports subdirectories like test/array)}
                            {--force : Overwrite the helper file if it already exists}
                            {--description= : Description for the helper file}
                            {--author= : Author of the helper file}';

    /**
     * @var string
     */
    protected $description = 'Create a new helper file with functions';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        $camelCaseName = $this->convertToCamelCase($name);

        if (! $this->isValidCamelCase($camelCaseName)) {
            $this->error('The name must be in CamelCase format (e.g., StringHelper, ArrayHelper).');
            $this->info('Valid examples: StringHelper, ArrayHelper, DateHelper, UserHelper');
            $this->info('For subdirectories: test/ArrayHelper, utils/StringHelper');
            $this->info('Auto-conversion applied: '.$name.' → '.$camelCaseName);

            return self::FAILURE;
        }

        if ($name !== $camelCaseName) {
            $this->info("Converting '{$name}' to '{$camelCaseName}'");
        }

        $path = $this->getHelperFilePath($camelCaseName);

        if (File::exists($path) && ! $this->option('force')) {
            $this->error("Helper file already exists: {$path}");
            $this->info('Use --force to overwrite the existing file.');

            return self::FAILURE;
        }

        $this->ensureHelperDirectoryExists($path);

        File::put($path, $this->buildHelperContent($camelCaseName));

        $this->info("Helper file created: {$path}");

        return self::SUCCESS;
    }

    protected function buildHelperContent(string $name): string
    {
        $stub = (string) file_get_contents(__DIR__.'/stubs/helper.stub');

        $nameParts = preg_split('/[\/\\\\]/', $name);
        $functionName = Str::camel(end($nameParts));
        $description = $this->option('description') ?: 'Helper functions for '.Str::studly(end($nameParts));
        $author = $this->option('author') ?: $this->resolveDefaultAuthor();
        $date = now()->format('Y-m-d H:i:s');

        return str_replace(
            ['{{functionName}}', '{{description}}', '{{author}}', '{{date}}'],
            [$functionName, $description, $author, $date],
            $stub
        );
    }
}
