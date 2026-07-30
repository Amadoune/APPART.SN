<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicProjectionRebuildArchitectureTest extends TestCase
{
    public function test_rebuild_application_is_framework_database_and_runtime_agnostic(): void
    {
        foreach ($this->files(dirname(__DIR__, 2).'/app/Application/PublicProjectionRebuild') as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|\\\\Infrastructure\\\\|\bPDO\b|\bSQL\b|\bSELECT\b|\bUPDATE\b|ServiceProvider|Controller|Queue|Scheduler)/i', $contents, $file);
        }
    }

    public function test_rebuild_decisions_are_exhaustive_without_default_branches(): void
    {
        foreach ($this->files(dirname(__DIR__, 2).'/app/Application/PublicProjectionRebuild') as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    /** @return list<string> */
    private function files(string $path): array
    {
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
