<?php

namespace Tests\Architecture;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LegacyMigrationContractsArchitectureTest extends TestCase
{
    public function test_contract_enclave_is_application_only_and_read_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Application/PublicRead';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        foreach (['LegacyMigrationInventoryReaderV1', 'LegacyMigrationWaveReaderV1', 'LegacyMigrationReconciliationReaderV1', 'LegacyMigrationQuarantineReaderV1', 'LegacyMigrationCutoverReaderV1'] as $reader) {
            self::assertStringContainsString('interface '.$reader, $php);
        }
        self::assertSame(5, substr_count($php, 'public function read('));
        self::assertStringNotContainsString('public function write(', $php);
        self::assertStringNotContainsString('public function append(', $php);
        foreach (['IdentityAccess', 'Professionals', 'ListingLifecycle', 'RealEstateCatalog', 'Media\\', 'ModerationReports', 'ContentSeo', 'Notifications', 'AdministrationConsole', 'Infrastructure\\', 'Persistence', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider', 'PDO', 'PostgreSql', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_result_catalogues_are_exactly_closed(): void
    {
        self::assertCount(4, LegacyMigrationInventoryStatusV1::cases());
        self::assertCount(6, LegacyMigrationWaveStatusV1::cases());
        self::assertCount(6, LegacyMigrationReconciliationStatusV1::cases());
        self::assertCount(5, LegacyMigrationQuarantineStatusV1::cases());
        self::assertCount(6, LegacyMigrationCutoverStatusV1::cases());
    }
}
