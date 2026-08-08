<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ReservationAvailabilityPublicReadContractsArchitectureTest extends TestCase
{
    #[Test]
    public function contracts_are_framework_agnostic_owner_scoped_and_have_no_implementation(): void
    {
        $root = dirname(__DIR__, 2);
        $directories = [
            $root.'/src/Modules/ListingLifecycle/Application/ReservationAvailabilityPublicRead',
            $root.'/src/Modules/RealEstateCatalog/Application/ReservationEligibilityPublicRead',
            $root.'/src/Modules/ReservationLifecycle/Application/ReservationAvailabilityPublicRead',
        ];
        $sources = '';

        foreach ($directories as $directory) {
            self::assertDirectoryExists($directory);
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                    $sources .= (string) file_get_contents($file->getPathname());
                }
            }
        }

        foreach (['Illuminate\\', 'Laravel', 'PDO', 'PostgreSQL', 'Repository', 'Store', 'Snapshot', 'Provider', 'Runtime', 'Controller', 'Event', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }

        self::assertStringContainsString('interface ListingReservationAvailabilityReaderV1', $sources);
        self::assertStringContainsString('interface PropertyReservationEligibilityReaderV1', $sources);
        self::assertStringContainsString('interface ReservationAvailabilityReaderV1', $sources);
        self::assertSame(3, substr_count($sources, 'interface '));
        self::assertSame(3, substr_count($sources, 'enum '));
        self::assertSame(9, substr_count($sources, 'final readonly class '));
        self::assertStringNotContainsString('ReservationLifecyclePersistence\\ReservationId', $sources);
    }

    #[Test]
    public function each_contract_enclave_has_no_cross_domain_import(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules';
        $enclaves = [
            'ListingLifecycle/Application/ReservationAvailabilityPublicRead' => ['RealEstateCatalog', 'ReservationLifecycle', 'IdentityAccess', 'ContactsLeads'],
            'RealEstateCatalog/Application/ReservationEligibilityPublicRead' => ['ListingLifecycle', 'ReservationLifecycle', 'IdentityAccess', 'ContactsLeads'],
            'ReservationLifecycle/Application/ReservationAvailabilityPublicRead' => ['ListingLifecycle', 'RealEstateCatalog', 'IdentityAccess', 'ContactsLeads'],
        ];

        foreach ($enclaves as $relative => $foreignModules) {
            $sources = '';
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$relative)) as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                    $sources .= (string) file_get_contents($file->getPathname());
                }
            }
            foreach ($foreignModules as $foreignModule) {
                self::assertStringNotContainsString('Modules\\'.$foreignModule.'\\', $sources);
            }
        }
    }
}
