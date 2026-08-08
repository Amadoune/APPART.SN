<?php

namespace Appart\Modules\LegacyMigration\Application\Runtime;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicLegacyMigrationRuntimeAvailabilityPolicy implements LegacyMigrationRuntimeAvailabilityPolicy
{
    private const PROBE_SUBJECT = 'runtime/legacy-migration-owner-source';

    public function __construct(private LegacyMigrationOwnerSource $source) {}

    public function inspect(): LegacyMigrationRuntimeAvailability
    {
        try {
            $subject = new LegacyMigrationSubjectKey(self::PROBE_SUBJECT);
            $observedAt = new LegacyMigrationObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $inventory = $this->source->readInventory($subject, $observedAt);
            $wave = $this->source->readWave($subject, $observedAt);
            $reconciliation = $this->source->readReconciliation($subject, $observedAt);
            $quarantine = $this->source->readQuarantine($subject, $observedAt);
            $cutover = $this->source->readCutover($subject, $observedAt);

            if ($inventory->status === LegacyMigrationInventoryStatusV1::DependencyUnavailable
                || $wave->status === LegacyMigrationWaveStatusV1::DependencyUnavailable
                || $reconciliation->status === LegacyMigrationReconciliationStatusV1::DependencyUnavailable
                || $quarantine->status === LegacyMigrationQuarantineStatusV1::DependencyUnavailable
                || $cutover->status === LegacyMigrationCutoverStatusV1::DependencyUnavailable) {
                return LegacyMigrationRuntimeAvailability::DependencyUnavailable;
            }

            if ($inventory->status === LegacyMigrationInventoryStatusV1::Corrupted
                || $wave->status === LegacyMigrationWaveStatusV1::Corrupted
                || $reconciliation->status === LegacyMigrationReconciliationStatusV1::Corrupted
                || $quarantine->status === LegacyMigrationQuarantineStatusV1::Corrupted
                || $cutover->status === LegacyMigrationCutoverStatusV1::Corrupted) {
                return LegacyMigrationRuntimeAvailability::Corrupted;
            }

            return LegacyMigrationRuntimeAvailability::Available;
        } catch (Throwable) {
            return LegacyMigrationRuntimeAvailability::DependencyUnavailable;
        }
    }
}
