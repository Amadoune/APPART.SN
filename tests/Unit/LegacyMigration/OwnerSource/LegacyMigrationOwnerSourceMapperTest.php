<?php

namespace Tests\Unit\LegacyMigration\OwnerSource;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveRevisionState;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\LegacyMigrationOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LegacyMigrationOwnerSourceMapperTest extends TestCase
{
    #[DataProvider('states')]
    public function test_round_trip_is_canonical(string $toRow, string $toState, object $state): void
    {
        $mapper = new LegacyMigrationOwnerSourceMapper;
        $row = $mapper->{$toRow}($state);
        $restored = $mapper->{$toState}($row);

        self::assertSame($state->subjectKey, $restored->subjectKey);
        self::assertSame($state->revision, $restored->revision);
        self::assertSame($state->decision, $restored->decision);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['revision_checksum']);
        self::assertSame('2026-08-03T10:00:00.123456Z', $row['effective_at']);
    }

    public function test_checksum_corruption_is_rejected(): void
    {
        $mapper = new LegacyMigrationOwnerSourceMapper;
        $row = $mapper->inventoryToRow(new LegacyMigrationInventoryRevisionState('scope:1', 1, LegacyMigrationInventoryStatusV1::Available, self::effective(), self::recorded()));
        $row['revision_checksum'] = str_repeat('0', 64);

        $this->expectException(RuntimeException::class);
        $mapper->toInventoryState($row);
    }

    /** @return iterable<string, array{string,string,object}> */
    public static function states(): iterable
    {
        yield 'inventory' => ['inventoryToRow', 'toInventoryState', new LegacyMigrationInventoryRevisionState('scope:1', 1, LegacyMigrationInventoryStatusV1::Available, self::effective(), self::recorded())];
        yield 'wave' => ['waveToRow', 'toWaveState', new LegacyMigrationWaveRevisionState('scope:1', 1, LegacyMigrationWaveStatusV1::Ready, self::effective(), self::recorded())];
        yield 'reconciliation' => ['reconciliationToRow', 'toReconciliationState', new LegacyMigrationReconciliationRevisionState('scope:1', 1, LegacyMigrationReconciliationStatusV1::Matched, self::effective(), self::recorded())];
        yield 'quarantine' => ['quarantineToRow', 'toQuarantineState', new LegacyMigrationQuarantineRevisionState('scope:1', 1, LegacyMigrationQuarantineStatusV1::Empty, self::effective(), self::recorded())];
        yield 'cutover' => ['cutoverToRow', 'toCutoverState', new LegacyMigrationCutoverRevisionState('scope:1', 1, LegacyMigrationCutoverStatusV1::Ready, self::effective(), self::recorded())];
    }

    private static function effective(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-03T12:00:00.123456+02:00');
    }

    private static function recorded(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-03T12:00:01.123456+02:00');
    }
}
