<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicMediaRevisionArchitectureTest extends TestCase
{
    public function test_foundation_is_application_only_and_infrastructure_agnostic(): void
    {
        foreach ($this->files() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|\\\\Infrastructure\\\\|\bPDO\b|\bSQL\b|ServiceProvider|Controller|Runtime|Http)/i', $contents, $file);
        }
    }

    public function test_revision_foundation_uses_no_clock_or_implicit_timestamp_version(): void
    {
        foreach ($this->files() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:DateTime|Carbon|clock|timestamp|microtime|time\s*\()/i', $contents, $file);
        }
    }

    public function test_promotion_decisions_have_no_default_branch(): void
    {
        foreach ($this->files() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    /** @return list<string> */
    private function files(): array
    {
        $files = [];
        $path = dirname(__DIR__, 2).'/app/Application/PublicMediaRevision';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
