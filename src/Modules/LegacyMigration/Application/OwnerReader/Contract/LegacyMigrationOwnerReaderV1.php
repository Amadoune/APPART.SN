<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader\Contract;

use Appart\Modules\LegacyMigration\Application\OwnerReader\LegacyMigrationOwnerReaderResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveReadResult;

interface LegacyMigrationOwnerReaderV1
{
    public function inventory(LegacyMigrationInventoryReadResult $result): LegacyMigrationOwnerReaderResult;

    public function wave(LegacyMigrationWaveReadResult $result): LegacyMigrationOwnerReaderResult;

    public function reconciliation(LegacyMigrationReconciliationReadResult $result): LegacyMigrationOwnerReaderResult;

    public function quarantine(LegacyMigrationQuarantineReadResult $result): LegacyMigrationOwnerReaderResult;

    public function cutover(LegacyMigrationCutoverReadResult $result): LegacyMigrationOwnerReaderResult;
}
