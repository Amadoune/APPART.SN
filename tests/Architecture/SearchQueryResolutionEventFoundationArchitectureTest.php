<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionEventFoundationArchitectureTest extends TestCase
{
    public function test_event_foundation_has_only_the_public_reader_as_decision_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionEvent';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertStringContainsString('PublicSearchQueryResolutionReaderV1', $php);
        foreach (['SearchQueryResolutionOwnerReader', 'SearchQueryResolutionOwnerSource', 'Runtime', 'PostgreSql', 'Mapper', 'PDO', 'SQL', 'Infrastructure\\', 'ListingLifecycle', 'Projection', 'Transport', 'Routing', 'Delivery', 'Outbox', 'SearchDocumentId', 'SearchIndexId', 'ListingId', 'checksum', 'diagnostic'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }
}
