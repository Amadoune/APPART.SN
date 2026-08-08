<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionContractsArchitectureTest extends TestCase
{
    public function test_contracts_are_framework_runtime_and_persistence_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolution';
        $sources = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            $sources .= $contents;
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Runtime', 'Provider', 'SearchDocumentId', 'SearchIndexId', 'ListingId', 'Ranking', 'Facet', 'Checksum', 'SourceRevisionSet'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
        self::assertStringContainsString('interface SearchQueryResolutionReaderV1', $sources);
        self::assertStringContainsString('enum SearchQueryResolutionStatusV1', $sources);
    }
}
