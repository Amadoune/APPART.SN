<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class SearchExperiencePublicReadContractsArchitectureTest extends TestCase
{
    #[Test]
    public function contract_is_owner_scoped_framework_agnostic_and_has_no_implementation(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchExperiencePublicRead';
        self::assertDirectoryExists($directory);
        $sources = '';

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $sources .= (string) file_get_contents($file->getPathname());
            }
        }

        foreach (['Illuminate\\', 'Laravel', 'PDO', 'PostgreSQL', 'Repository', 'Store', 'Mapper', 'Snapshot', 'Provider', 'Runtime', 'Controller', 'Http', 'Event', 'Outbox', 'Projection', 'Cache', 'ListingLifecycle', 'ContentSeo'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertStringContainsString('interface SearchQueryReaderV1', $sources);
        self::assertStringContainsString('enum SearchQueryResultStatusV1', $sources);
        self::assertSame(1, substr_count($sources, 'interface '));
        self::assertSame(1, substr_count($sources, 'enum '));
        self::assertSame(3, substr_count($sources, 'final readonly class '));
        self::assertStringNotContainsString('function now(', $sources);
        foreach (['score', 'ranking', 'facet', 'pagination', 'snippet', 'checksum', 'version'] as $forbiddenPayload) {
            self::assertStringNotContainsString($forbiddenPayload, strtolower($sources));
        }
    }
}
