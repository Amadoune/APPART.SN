<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionOwnerSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_port_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionOwnerSource';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Runtime', 'Provider', 'Http', 'Event', 'Outbox', 'SearchDocumentId'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    #[Test]
    public function migration_is_additive_owner_local_and_private(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/';
        $sources = (string) file_get_contents($root.'SearchQueryResolutionOwnerSourceMapper.php').(string) file_get_contents($root.'PostgreSql/PostgreSqlSearchQueryResolutionOwnerSource.php');
        foreach (['ListingLifecycle', 'IdentityAccess', 'ReservationLifecycle', 'PublicProjection', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
        $migration = (string) file_get_contents($root.'PostgreSql/Migrations/076_search_query_resolution_owner_source.sql');
        foreach (['FOREIGN KEY', 'REFERENCES ', 'CASCADE', 'TRIGGER', 'listing_id', 'account_id', 'email', 'phone', 'query_text', 'document_id'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $migration);
        }
        self::assertStringContainsString('search_query_resolution_revision_journal', $migration);
        self::assertStringContainsString('search_query_resolution_current_index', $migration);
    }
}
