<?php

namespace Devtical\Helpers\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HelperMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:helper 
                            {name : The name of the helper file (supports subdirectories like test/array)}
                            {--force : Overwrite the helper file if it already exists}
                            {--description= : Description for the helper file}
                            {--author= : Author of the helper file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new helper file with functions';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Helper';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/helper.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\'.config('helpers.directory', 'Helpers');
    }

    /**
     * Get the destination class path.
     * Supports subdirectories by parsing the name for directory structure.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $directory = app_path(config('helpers.directory', 'Helpers'));

        // Check if name contains subdirectory (e.g., "test/array" or "test\array")
        $nameParts = preg_split('/[\/\\\\]/', $name);

        if (count($nameParts) > 1) {
            // Extract directory path and filename
            $dirParts = array_slice($nameParts, 0, -1);
            $filename = Str::studly(end($nameParts)).'.php';

            // Convert all directory parts to CamelCase
            $camelCaseDirParts = array_map(function ($part) {
                return Str::studly($part);
            }, $dirParts);

            $subDir = implode('/', $camelCaseDirParts);

            // Create full path with subdirectory
            $fullPath = $directory.'/'.$subDir.'/'.$filename;

            // Ensure subdirectory exists
            $subDirPath = $directory.'/'.$subDir;
            if (! File::exists($subDirPath)) {
                File::makeDirectory($subDirPath, 0755, true);
                $this->info("Created subdirectory: {$subDirPath}");
            }

            return $fullPath;
        }

        // No subdirectory, use original logic
        $filename = Str::studly($name).'.php';

        return $directory.'/'.$filename;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $filename = Str::studly($name);

        // For subdirectories, extract only the filename part for function name
        $nameParts = preg_split('/[\/\\\\]/', $name);
        $functionName = Str::camel(end($nameParts));

        $description = $this->option('description') ?: "Helper functions for {$filename}";
        $author = $this->option('author') ?: 'Laravel Helper';
        $date = now()->format('Y-m-d H:i:s');

        return $this->replaceNamespace($stub, $name)
            ->replaceFunctionName($stub, $functionName)
            ->replaceDescription($stub, $description)
            ->replaceAuthor($stub, $author)
            ->replaceDate($stub, $date)
            ->replaceClass($stub, $filename);
    }

    /**
     * Replace the function name in the stub.
     *
     * @param  string  $stub
     * @param  string  $functionName
     * @return $this
     */
    protected function replaceFunctionName(&$stub, $functionName)
    {
        $stub = str_replace('{{functionName}}', $functionName, $stub);

        return $this;
    }

    /**
     * Replace the description in the stub.
     *
     * @param  string  $stub
     * @param  string  $description
     * @return $this
     */
    protected function replaceDescription(&$stub, $description)
    {
        $stub = str_replace('{{description}}', $description, $stub);

        return $this;
    }

    /**
     * Replace the author in the stub.
     *
     * @param  string  $stub
     * @param  string  $author
     * @return $this
     */
    protected function replaceAuthor(&$stub, $author)
    {
        $stub = str_replace('{{author}}', $author, $stub);

        return $this;
    }

    /**
     * Replace the date in the stub.
     *
     * @param  string  $stub
     * @param  string  $date
     * @return $this
     */
    protected function replaceDate(&$stub, $date)
    {
        $stub = str_replace('{{date}}', $date, $stub);

        return $this;
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name = $this->getNameInput();

        // Convert to CamelCase and validate
        $camelCaseName = $this->convertToCamelCase($name);

        if (! $this->isValidCamelCase($camelCaseName)) {
            $this->error('The name must be in CamelCase format (e.g., StringHelper, ArrayHelper).');
            $this->info('Valid examples: StringHelper, ArrayHelper, DateHelper, UserHelper');
            $this->info('For subdirectories: test/ArrayHelper, utils/StringHelper');
            $this->info('Auto-conversion applied: '.$name.' → '.$camelCaseName);

            return false;
        }

        // Show conversion if different from input
        if ($name !== $camelCaseName) {
            $this->info("Converting '{$name}' to '{$camelCaseName}'");
        }

        $path = $this->getPath($camelCaseName);

        // Check if file already exists
        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->error("Helper file already exists: {$path}");
            $this->info('Use --force to overwrite the existing file.');

            return false;
        }

        // Create the directory if it doesn't exist
        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info("Created directory: {$directory}");
        }

        // Generate the file
        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($camelCaseName));

        $this->info("Helper file created: {$path}");

        return true;
    }

    /**
     * Convert input to CamelCase format with auto-prefix.
     * Supports subdirectories by converting all parts to CamelCase.
     *
     * @param  string  $name
     * @return string
     */
    protected function convertToCamelCase($name)
    {
        // Remove any non-alphanumeric characters except spaces, hyphens, underscores, and forward slashes
        $name = preg_replace('/[^a-zA-Z0-9\s\-_\/]/', '', $name);

        // Check if name contains subdirectory
        $nameParts = preg_split('/[\/\\\\]/', $name);

        if (count($nameParts) > 1) {
            // Convert all parts to CamelCase
            $camelCaseParts = array_map(function ($part) {
                // Convert snake_case and kebab-case to CamelCase
                $part = str_replace(['-', '_'], ' ', $part);

                return Str::studly($part);
            }, $nameParts);

            $name = implode('/', $camelCaseParts);
        } else {
            // Convert snake_case and kebab-case to CamelCase
            $name = str_replace(['-', '_'], ' ', $name);
            $name = Str::studly($name);
        }

        // Add "Helper" suffix if not already present
        if (! $this->hasHelperSuffix($name)) {
            $name = $name.'Helper';
        }

        return $name;
    }

    /**
     * Check if the name already has "Helper" suffix.
     *
     * @param  string  $name
     * @return bool
     */
    protected function hasHelperSuffix($name)
    {
        // Check if name ends with "Helper" (case sensitive)
        return str_ends_with($name, 'Helper');
    }

    /**
     * Check if the name is valid CamelCase.
     * For subdirectories, only validate the filename part.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isValidCamelCase($name)
    {
        // Check if name contains subdirectory
        $nameParts = preg_split('/[\/\\\\]/', $name);

        if (count($nameParts) > 1) {
            // For subdirectories, validate only the filename part
            $filename = end($nameParts);

            return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $filename);
        }

        // Must start with uppercase letter and contain only letters and numbers
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name);
    }

    /**
     * Check if the name is valid (legacy method for backward compatibility).
     *
     * @param  string  $name
     * @return bool
     */
    protected function isValidName($name)
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }
}
