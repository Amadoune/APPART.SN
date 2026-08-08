<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_the_event_contract(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionDelivery';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertStringContainsString('SearchQueryResolutionEventV1', $php);
        foreach (['SearchExperiencePublicRead\\SearchQuery', 'Reader', 'OwnerSource', 'Runtime', 'PostgreSql', 'Mapper', 'PDO', 'SQL', 'Infrastructure\\', 'Http\\', 'Transport', 'Routing', 'Outbox', 'SearchDocumentId', 'SearchIndexId', 'ListingId', 'checksum', 'diagnostic'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }
}
