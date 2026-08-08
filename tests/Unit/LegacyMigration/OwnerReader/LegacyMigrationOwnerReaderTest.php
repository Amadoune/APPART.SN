<?php

namespace Tests\Unit\LegacyMigration\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationCutoverOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationInventoryOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationOwnerReaderPolicy;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationQuarantineOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationReconciliationOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationWaveOwnerReader;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveRevisionState;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LegacyMigrationOwnerReaderTest extends TestCase
{
    public function test_inventory_reduction_is_exhaustive_and_homonymous(): void
    {
        $cases = [
            LegacyMigrationInventoryReadResult::found(new LegacyMigrationInventoryRevisionState($this->subject(), 1, LegacyMigrationInventoryStatusV1::Available, $this->effectiveAt(), $this->recordedAt())),
            LegacyMigrationInventoryReadResult::missing(),
            LegacyMigrationInventoryReadResult::corrupted(),
            LegacyMigrationInventoryReadResult::dependencyUnavailable(),
        ];
        foreach ($cases as $owner) {
            $source = $this->createMock(LegacyMigrationOwnerSource::class);
            $source->method('readInventory')->willReturn($owner);
            $result = (new LegacyMigrationInventoryOwnerReader($source, new LegacyMigrationOwnerReaderPolicy))->read($this->subject(), $this->observedAt());
            self::assertSame($owner->status, $result->status);
            self::assertSame($this->observedAt()->canonical(), $result->observedAt);
        }
    }

    public function test_wave_reduction_is_exhaustive_and_homonymous(): void
    {
        $cases = [];
        foreach ([LegacyMigrationWaveStatusV1::Ready, LegacyMigrationWaveStatusV1::Blocked, LegacyMigrationWaveStatusV1::Completed] as $status) {
            $cases[] = LegacyMigrationWaveReadResult::found(new LegacyMigrationWaveRevisionState($this->subject(), 1, $status, $this->effectiveAt(), $this->recordedAt()));
        }
        $cases[] = LegacyMigrationWaveReadResult::missing();
        $cases[] = LegacyMigrationWaveReadResult::corrupted();
        $cases[] = LegacyMigrationWaveReadResult::dependencyUnavailable();
        foreach ($cases as $owner) {
            $source = $this->createMock(LegacyMigrationOwnerSource::class);
            $source->method('readWave')->willReturn($owner);
            $result = (new LegacyMigrationWaveOwnerReader($source, new LegacyMigrationOwnerReaderPolicy))->read($this->subject(), $this->observedAt());
            self::assertSame($owner->status, $result->status);
        }
    }

    public function test_reconciliation_reduction_is_exhaustive_and_homonymous(): void
    {
        $cases = [];
        foreach ([LegacyMigrationReconciliationStatusV1::Matched, LegacyMigrationReconciliationStatusV1::Divergent, LegacyMigrationReconciliationStatusV1::Pending] as $status) {
            $cases[] = LegacyMigrationReconciliationReadResult::found(new LegacyMigrationReconciliationRevisionState($this->subject(), 1, $status, $this->effectiveAt(), $this->recordedAt()));
        }
        $cases[] = LegacyMigrationReconciliationReadResult::missing();
        $cases[] = LegacyMigrationReconciliationReadResult::corrupted();
        $cases[] = LegacyMigrationReconciliationReadResult::dependencyUnavailable();
        foreach ($cases as $owner) {
            $source = $this->createMock(LegacyMigrationOwnerSource::class);
            $source->method('readReconciliation')->willReturn($owner);
            $result = (new LegacyMigrationReconciliationOwnerReader($source, new LegacyMigrationOwnerReaderPolicy))->read($this->subject(), $this->observedAt());
            self::assertSame($owner->status, $result->status);
        }
    }

    public function test_quarantine_reduction_is_exhaustive_and_homonymous(): void
    {
        $cases = [];
        foreach ([LegacyMigrationQuarantineStatusV1::Empty, LegacyMigrationQuarantineStatusV1::ContainsItems] as $status) {
            $cases[] = LegacyMigrationQuarantineReadResult::found(new LegacyMigrationQuarantineRevisionState($this->subject(), 1, $status, $this->effectiveAt(), $this->recordedAt()));
        }
        $cases[] = LegacyMigrationQuarantineReadResult::missing();
        $cases[] = LegacyMigrationQuarantineReadResult::corrupted();
        $cases[] = LegacyMigrationQuarantineReadResult::dependencyUnavailable();
        foreach ($cases as $owner) {
            $source = $this->createMock(LegacyMigrationOwnerSource::class);
            $source->method('readQuarantine')->willReturn($owner);
            $result = (new LegacyMigrationQuarantineOwnerReader($source, new LegacyMigrationOwnerReaderPolicy))->read($this->subject(), $this->observedAt());
            self::assertSame($owner->status, $result->status);
        }
    }

    public function test_cutover_reduction_is_exhaustive_and_homonymous(): void
    {
        $cases = [];
        foreach ([LegacyMigrationCutoverStatusV1::Ready, LegacyMigrationCutoverStatusV1::Blocked, LegacyMigrationCutoverStatusV1::Completed] as $status) {
            $cases[] = LegacyMigrationCutoverReadResult::found(new LegacyMigrationCutoverRevisionState($this->subject(), 1, $status, $this->effectiveAt(), $this->recordedAt()));
        }
        $cases[] = LegacyMigrationCutoverReadResult::missing();
        $cases[] = LegacyMigrationCutoverReadResult::corrupted();
        $cases[] = LegacyMigrationCutoverReadResult::dependencyUnavailable();
        foreach ($cases as $owner) {
            $source = $this->createMock(LegacyMigrationOwnerSource::class);
            $source->method('readCutover')->willReturn($owner);
            $result = (new LegacyMigrationCutoverOwnerReader($source, new LegacyMigrationOwnerReaderPolicy))->read($this->subject(), $this->observedAt());
            self::assertSame($owner->status, $result->status);
        }
    }

    private function subject(): LegacyMigrationSubjectKey
    {
        return new LegacyMigrationSubjectKey('legacy-migration:owner-reader-test');
    }

    private function observedAt(): LegacyMigrationObservedAt
    {
        return new LegacyMigrationObservedAt(new DateTimeImmutable('2026-08-04T12:00:00Z'));
    }

    private function effectiveAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T10:00:00Z');
    }

    private function recordedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T10:00:01Z');
    }
}
