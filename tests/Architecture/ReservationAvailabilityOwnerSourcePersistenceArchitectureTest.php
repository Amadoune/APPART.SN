<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReservationAvailabilityOwnerSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function application_contracts_are_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationAvailabilityOwnerSource';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Runtime\\', 'App\\Providers', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    #[Test]
    public function persistence_is_owner_local_and_has_no_cross_domain_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence';
        $files = [
            $root.'/ReservationAvailabilityOwnerSourceMapper.php',
            $root.'/PostgreSql/PostgreSqlReservationAvailabilityOwnerSource.php',
        ];
        $sources = '';
        foreach ($files as $file) {
            $content = file_get_contents($file);
            self::assertIsString($content);
            $sources .= $content;
        }
        foreach (['ListingLifecycle', 'RealEstateCatalog', 'IdentityAccess', 'ContactsLeads', 'AccountId', 'ListingId', 'PropertyId', 'Application\\ReservationAvailabilityOwnerSourceRuntime', 'Provider', 'ReaderV1'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
    }

    #[Test]
    public function migration_is_additive_owner_local_and_contains_no_pii_or_cross_domain_key(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
        $migration = file_get_contents($root.'074_reservation_availability_owner_local_source.sql');
        $rollback = file_get_contents($root.'074_reservation_availability_owner_local_source.down.sql');
        self::assertIsString($migration);
        self::assertIsString($rollback);
        foreach (['FOREIGN KEY', 'REFERENCES ', 'CASCADE', 'TRIGGER', 'account_id', 'listing_id', 'property_id', 'email', 'phone', 'address'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $migration);
        }
        self::assertStringContainsString('availability_intent_revisions', $migration);
        self::assertStringContainsString('DROP TABLE IF EXISTS reservation_lifecycle.availability_intent_revisions', $rollback);
    }
}
