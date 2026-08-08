<?php

namespace Tests\Unit\LegacyMigration\PublicRead;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyMigrationContractsTest extends TestCase
{
    #[DataProvider('results')]
    public function test_every_result_is_closed_and_limited_to_status_and_canonical_observed_at(object $result, string $expectedStatus): void
    {
        self::assertSame($expectedStatus, $result->status->value);
        self::assertSame('2026-08-03T10:00:00.123456Z', $result->observedAt);
        $properties = array_keys(get_object_vars($result));
        sort($properties);
        self::assertSame(['observedAt', 'status'], $properties);
    }

    /** @return iterable<string, array{object, string}> */
    public static function results(): iterable
    {
        $observedAt = self::observedAt();
        foreach (LegacyMigrationInventoryStatusV1::cases() as $status) {
            yield 'inventory '.$status->value => [new LegacyMigrationInventoryResultV1($status, $observedAt), $status->value];
        }
        foreach (LegacyMigrationWaveStatusV1::cases() as $status) {
            yield 'wave '.$status->value => [new LegacyMigrationWaveResultV1($status, $observedAt), $status->value];
        }
        foreach (LegacyMigrationReconciliationStatusV1::cases() as $status) {
            yield 'reconciliation '.$status->value => [new LegacyMigrationReconciliationResultV1($status, $observedAt), $status->value];
        }
        foreach (LegacyMigrationQuarantineStatusV1::cases() as $status) {
            yield 'quarantine '.$status->value => [new LegacyMigrationQuarantineResultV1($status, $observedAt), $status->value];
        }
        foreach (LegacyMigrationCutoverStatusV1::cases() as $status) {
            yield 'cutover '.$status->value => [new LegacyMigrationCutoverResultV1($status, $observedAt), $status->value];
        }
    }

    public function test_subject_key_is_opaque_and_canonical(): void
    {
        self::assertSame('migration:scope:1', (new LegacyMigrationSubjectKey('migration:scope:1'))->canonical());
    }

    #[DataProvider('invalidSubjectKeys')]
    public function test_subject_key_rejects_non_canonical_values(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LegacyMigrationSubjectKey($value);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidSubjectKeys(): iterable
    {
        yield 'empty' => [''];
        yield 'leading space' => [' invalid'];
        yield 'trailing space' => ['invalid '];
        yield 'too long' => [str_repeat('x', 256)];
    }

    private static function observedAt(): LegacyMigrationObservedAt
    {
        return new LegacyMigrationObservedAt(new DateTimeImmutable('2026-08-03T12:00:00.123456+02:00'));
    }
}
