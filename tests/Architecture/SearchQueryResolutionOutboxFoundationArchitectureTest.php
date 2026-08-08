<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionOutboxFoundationArchitectureTest extends TestCase
{
    public function test_outbox_application_has_delivery_as_its_only_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionOutbox';
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/*.php') ?: []));
        self::assertStringContainsString('SearchQueryResolutionDeliveryV1', $php);
        foreach (['SearchQueryResolutionEvent', 'PublicSearchQueryResolutionReader', 'SearchQueryResolutionOwnerReader', 'Runtime', 'PostgreSql', 'PDO', 'SQL', 'Http\\', 'Transport', 'Routing', 'Consumer', 'SearchDocumentId', 'SearchIndexId', 'ListingId'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_076_is_unchanged(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/076_search_query_resolution_owner_source.sql');
        self::assertSame('4439ef5640e9349b50aeb8e7166c66ac4fb05d6ae41b94e90c9cec0467a963b2', hash('sha256', $migration));
    }
}
