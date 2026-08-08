<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionOwnerReaderFoundationArchitectureTest extends TestCase
{
    public function test_public_reader_does_not_cross_forbidden_boundaries(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionPublicReader';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertStringContainsString('SearchQueryResolutionOwnerReader', $php);
        foreach (['SearchQueryResolutionOwnerSource', 'OwnerSourceRuntime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'RuntimeHealth', 'ListingLifecycle', 'Projection', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'SearchDocumentId', 'SearchIndexId', 'ListingId'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }
}
