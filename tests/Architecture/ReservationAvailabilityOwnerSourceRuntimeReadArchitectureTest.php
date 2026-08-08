<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReservationAvailabilityOwnerSourceRuntimeReadArchitectureTest extends TestCase
{
    public function test_application_runtime_read_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationAvailabilityOwnerSourceRuntimeRead';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'RuntimeHealth', 'OwnerReader', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_has_unique_owner_scoped_runtime_read_binding(): void
    {
        $provider = dirname(__DIR__, 2).'/app/Providers/ReservationAvailabilityOwnerSourceRuntimeReadServiceProvider.php';
        $contents = file_get_contents($provider);
        self::assertIsString($contents);
        self::assertSame(1, preg_match_all('/->alias\(\s+DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1::class,\s+ReservationAvailabilityOwnerSourceRuntimeReadV1::class,/s', $contents));
        self::assertStringContainsString('singleton', $contents);
        foreach (['PostgreSql', 'Mapper', 'PDO', 'RuntimeHealth', 'OwnerReader'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_only_certified_owner_reader_and_provider_are_admitted_without_persistence_leak(): void
    {
        $root = dirname(__DIR__, 2);
        $readerDirectory = $root.'/src/Modules/ReservationLifecycle/Application/ReservationAvailabilityOwnerReader';
        $readerFiles = glob($readerDirectory.'/*.php');
        self::assertIsArray($readerFiles);
        self::assertSame(
            [$readerDirectory.'/OwnerReservationAvailabilityReaderV1.php'],
            array_values($readerFiles),
        );

        $reader = file_get_contents($readerFiles[0]);
        self::assertIsString($reader);
        self::assertStringContainsString('implements ReservationAvailabilityReaderV1', $reader);
        self::assertStringContainsString('ReservationAvailabilityOwnerSourceRuntimeReadV1', $reader);
        foreach ([
            'Application\\ReservationAvailabilityOwnerSource\\',
            'PostgreSqlReservationAvailabilityOwnerSource',
            'Infrastructure\\',
            'Mapper',
            'State',
            'Store',
            'PDO',
            'PostgreSQL',
            'RuntimeHealth',
            'App\\Http',
            'Event\\',
            'Delivery\\',
            'Outbox\\',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $reader);
        }

        $providerPath = $root.'/app/Providers/ReservationAvailabilityOwnerReaderServiceProvider.php';
        $provider = file_get_contents($providerPath);
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'singleton(OwnerReservationAvailabilityReaderV1::class)'));
        self::assertSame(1, substr_count($provider, 'alias(OwnerReservationAvailabilityReaderV1::class, ReservationAvailabilityReaderV1::class)'));
        foreach (['PostgreSql', 'Infrastructure\\', 'Mapper', 'State', 'Store', 'PDO', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }

        $migration = file_get_contents($root.'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/074_reservation_availability_owner_local_source.sql');
        self::assertIsString($migration);
        self::assertSame('6fb493d3ffbcebfd77e887d46a484baa93583ddb28b0786bb6f753c1858d9171', hash('sha256', $migration));
    }
}
