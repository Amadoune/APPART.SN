<?php

namespace Tests\Unit\LegacyMigration\Http;

use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyMigrationResponseFactoryTest extends TestCase
{
    #[DataProvider('mappings')]
    public function test_http_mapping_is_exhaustive(string $kind, object $result, int $expected): void
    {
        $response = (new LegacyMigrationResponseFactory)->{$kind}($result);

        self::assertSame($expected, $response->getStatusCode());
        self::assertArrayHasKey('status', $response->getData(true));
        self::assertArrayHasKey('observedAt', $response->getData(true));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    /** @return iterable<string, array{string, LegacyMigrationInventoryResultV1|LegacyMigrationWaveResultV1|LegacyMigrationReconciliationResultV1|LegacyMigrationQuarantineResultV1|LegacyMigrationCutoverResultV1, int}> */
    public static function mappings(): iterable
    {
        $at = new LegacyMigrationObservedAt(new DateTimeImmutable('2026-08-04T12:00:00Z'));
        foreach ([[LegacyMigrationInventoryStatusV1::Available, 200], [LegacyMigrationInventoryStatusV1::Missing, 404], [LegacyMigrationInventoryStatusV1::Corrupted, 503], [LegacyMigrationInventoryStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'inventory '.$status->value => ['inventory', new LegacyMigrationInventoryResultV1($status, $at), $http];
        }
        foreach ([[LegacyMigrationWaveStatusV1::Ready, 200], [LegacyMigrationWaveStatusV1::Blocked, 200], [LegacyMigrationWaveStatusV1::Completed, 200], [LegacyMigrationWaveStatusV1::Missing, 404], [LegacyMigrationWaveStatusV1::Corrupted, 503], [LegacyMigrationWaveStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'wave '.$status->value => ['wave', new LegacyMigrationWaveResultV1($status, $at), $http];
        }
        foreach ([[LegacyMigrationReconciliationStatusV1::Matched, 200], [LegacyMigrationReconciliationStatusV1::Divergent, 200], [LegacyMigrationReconciliationStatusV1::Pending, 200], [LegacyMigrationReconciliationStatusV1::Missing, 404], [LegacyMigrationReconciliationStatusV1::Corrupted, 503], [LegacyMigrationReconciliationStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'reconciliation '.$status->value => ['reconciliation', new LegacyMigrationReconciliationResultV1($status, $at), $http];
        }
        foreach ([[LegacyMigrationQuarantineStatusV1::Empty, 200], [LegacyMigrationQuarantineStatusV1::ContainsItems, 200], [LegacyMigrationQuarantineStatusV1::Missing, 404], [LegacyMigrationQuarantineStatusV1::Corrupted, 503], [LegacyMigrationQuarantineStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'quarantine '.$status->value => ['quarantine', new LegacyMigrationQuarantineResultV1($status, $at), $http];
        }
        foreach ([[LegacyMigrationCutoverStatusV1::Ready, 200], [LegacyMigrationCutoverStatusV1::Blocked, 200], [LegacyMigrationCutoverStatusV1::Completed, 200], [LegacyMigrationCutoverStatusV1::Missing, 404], [LegacyMigrationCutoverStatusV1::Corrupted, 503], [LegacyMigrationCutoverStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'cutover '.$status->value => ['cutover', new LegacyMigrationCutoverResultV1($status, $at), $http];
        }
    }
}
