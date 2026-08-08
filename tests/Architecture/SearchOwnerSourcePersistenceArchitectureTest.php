<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchOwnerSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_port_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchOwnerSource';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Runtime', 'Provider', 'Http', 'Event', 'Outbox'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    #[Test]
    public function persistence_is_owner_local_and_the_migration_is_additive(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/';
        $sources = (string) file_get_contents($root.'SearchOwnerSourceMapper.php').(string) file_get_contents($root.'PostgreSql/PostgreSqlSearchOwnerSource.php');
        foreach (['ListingLifecycle', 'IdentityAccess', 'ContactsLeads', 'ReservationLifecycle', 'ReaderV1', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        $migration = (string) file_get_contents($root.'PostgreSql/Migrations/075_search_owner_source.sql');
        foreach (['FOREIGN KEY', 'REFERENCES ', 'CASCADE', 'TRIGGER', 'account_id', 'email', 'phone', 'address'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $migration);
        }
        self::assertStringContainsString('search_owner_revision_journal', $migration);
        self::assertStringContainsString('search_owner_current_index', $migration);
    }
}
