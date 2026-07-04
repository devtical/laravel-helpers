<?php

namespace Devtical\Helpers\Concerns;

trait InteractsWithHelperFiles
{
    /**
     * @return list<string>
     */
    protected function extractFunctionNames(string $content): array
    {
        preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches);

        return $matches[1];
    }

    /**
     * @return array{valid: bool, message: string|null}
     */
    protected function validatePhpSyntax(string $file): array
    {
        $content = file_get_contents($file);

        if ($content === false) {
            return ['valid' => false, 'message' => 'Unable to read file.'];
        }

        try {
            $tokens = token_get_all($content, TOKEN_PARSE);
            unset($tokens);
        } catch (\ParseError $exception) {
            return ['valid' => false, 'message' => $exception->getMessage()];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * @param  list<string>  $files
     * @return array<string, list<string>>
     */
    protected function findDuplicateFunctions(array $files): array
    {
        $functions = [];
        $duplicates = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            foreach ($this->extractFunctionNames($content) as $functionName) {
                if (isset($functions[$functionName])) {
                    $duplicates[$functionName][] = $file;

                    if (! in_array($functions[$functionName], $duplicates[$functionName], true)) {
                        array_unshift($duplicates[$functionName], $functions[$functionName]);
                    }

                    continue;
                }

                $functions[$functionName] = $file;
            }
        }

        return $duplicates;
    }
}
