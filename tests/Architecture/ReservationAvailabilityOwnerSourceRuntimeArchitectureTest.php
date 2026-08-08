<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReservationAvailabilityOwnerSourceRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationAvailabilityOwnerSourceRuntime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Reader', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_is_owner_scoped_and_bindings_are_unique(): void
    {
        $provider = dirname(__DIR__, 2).'/app/Providers/ReservationAvailabilityOwnerSourceRuntimeServiceProvider.php';
        $contents = file_get_contents($provider);
        self::assertIsString($contents);
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlReservationAvailabilityOwnerSource::class, ReservationAvailabilityOwnerSource::class\)/', $contents));
        self::assertSame(1, preg_match_all('/->alias\(\s+DeterministicReservationAvailabilityOwnerSourceRuntimeV1::class,\s+ReservationAvailabilityOwnerSourceRuntimeV1::class,/s', $contents));
        self::assertStringContainsString('singleton', $contents);
        foreach (['ContactsLeads', 'ListingLifecycle', 'RealEstateCatalog', 'IdentityAccess', 'RuntimeHealthInspector'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_runtime_preserves_migration_and_forbidden_surfaces(): void
    {
        $application = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationAvailabilityOwnerSourceRuntime';
        self::assertDirectoryDoesNotExist($application.'/Reader');
        $migration = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/074_reservation_availability_owner_local_source.sql');
        self::assertIsString($migration);
        self::assertSame('6fb493d3ffbcebfd77e887d46a484baa93583ddb28b0786bb6f753c1858d9171', hash('sha256', $migration));
    }
}
