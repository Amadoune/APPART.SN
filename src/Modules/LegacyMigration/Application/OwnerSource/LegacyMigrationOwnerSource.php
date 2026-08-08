<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

interface LegacyMigrationOwnerSource
{
    public function appendInventory(LegacyMigrationInventoryRevisionState $revision): LegacyMigrationInventoryWriteResult;

    public function appendWave(LegacyMigrationWaveRevisionState $revision): LegacyMigrationWaveWriteResult;

    public function appendReconciliation(LegacyMigrationReconciliationRevisionState $revision): LegacyMigrationReconciliationWriteResult;

    public function appendQuarantine(LegacyMigrationQuarantineRevisionState $revision): LegacyMigrationQuarantineWriteResult;

    public function appendCutover(LegacyMigrationCutoverRevisionState $revision): LegacyMigrationCutoverWriteResult;

    public function readInventory(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationInventoryReadResult;

    public function readWave(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationWaveReadResult;

    public function readReconciliation(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationReconciliationReadResult;

    public function readQuarantine(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationQuarantineReadResult;

    public function readCutover(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationCutoverReadResult;
}
