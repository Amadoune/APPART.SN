<?php

namespace Tests\Unit\LegacyMigration\Runtime;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveReadResult;
use Appart\Modules\LegacyMigration\Application\Runtime\DeterministicLegacyMigrationRuntime;
use Appart\Modules\LegacyMigration\Application\Runtime\DeterministicLegacyMigrationRuntimeAvailabilityPolicy;
use Appart\Modules\LegacyMigration\Application\Runtime\LegacyMigrationRuntimeAvailability;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LegacyMigrationRuntimeTest extends TestCase
{
    public function test_missing_on_all_streams_is_technically_available(): void
    {
        $source = $this->sourceWith(
            LegacyMigrationInventoryReadResult::missing(),
            LegacyMigrationWaveReadResult::missing(),
            LegacyMigrationReconciliationReadResult::missing(),
            LegacyMigrationQuarantineReadResult::missing(),
            LegacyMigrationCutoverReadResult::missing(),
        );

        self::assertSame(LegacyMigrationRuntimeAvailability::Available, (new DeterministicLegacyMigrationRuntimeAvailabilityPolicy($source))->inspect());
    }

    public function test_corruption_and_dependency_failure_are_reduced_deterministically(): void
    {
        $corrupted = $this->sourceWith(
            LegacyMigrationInventoryReadResult::corrupted(),
            LegacyMigrationWaveReadResult::missing(),
            LegacyMigrationReconciliationReadResult::missing(),
            LegacyMigrationQuarantineReadResult::missing(),
            LegacyMigrationCutoverReadResult::missing(),
        );
        $dependencyUnavailable = $this->sourceWith(
            LegacyMigrationInventoryReadResult::corrupted(),
            LegacyMigrationWaveReadResult::dependencyUnavailable(),
            LegacyMigrationReconciliationReadResult::missing(),
            LegacyMigrationQuarantineReadResult::missing(),
            LegacyMigrationCutoverReadResult::missing(),
        );

        self::assertSame(LegacyMigrationRuntimeAvailability::Corrupted, (new DeterministicLegacyMigrationRuntimeAvailabilityPolicy($corrupted))->inspect());
        self::assertSame(LegacyMigrationRuntimeAvailability::DependencyUnavailable, (new DeterministicLegacyMigrationRuntimeAvailabilityPolicy($dependencyUnavailable))->inspect());
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_minimal(): void
    {
        $source = $this->createMock(LegacyMigrationOwnerSource::class);
        $source->method('readInventory')->willThrowException(new RuntimeException('technical'));
        $runtime = new DeterministicLegacyMigrationRuntime(new DeterministicLegacyMigrationRuntimeAvailabilityPolicy($source));

        self::assertSame(LegacyMigrationRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame([
            'runtimeId' => 'legacy-migration.owner-source',
            'version' => 'legacy-migration-runtime-v1',
            'availability' => LegacyMigrationRuntimeAvailability::DependencyUnavailable,
        ], get_object_vars($runtime->diagnostics()));
    }

    private function sourceWith(
        LegacyMigrationInventoryReadResult $inventory,
        LegacyMigrationWaveReadResult $wave,
        LegacyMigrationReconciliationReadResult $reconciliation,
        LegacyMigrationQuarantineReadResult $quarantine,
        LegacyMigrationCutoverReadResult $cutover,
    ): LegacyMigrationOwnerSource {
        $source = $this->createMock(LegacyMigrationOwnerSource::class);
        $source->method('readInventory')->willReturn($inventory);
        $source->method('readWave')->willReturn($wave);
        $source->method('readReconciliation')->willReturn($reconciliation);
        $source->method('readQuarantine')->willReturn($quarantine);
        $source->method('readCutover')->willReturn($cutover);

        return $source;
    }
}
