<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveReadResult;

final readonly class LegacyMigrationOwnerReaderPolicy implements LegacyMigrationOwnerReaderV1
{
    public function inventory(LegacyMigrationInventoryReadResult $result): LegacyMigrationOwnerReaderResult
    {
        return new LegacyMigrationOwnerReaderResult(LegacyMigrationOwnerReaderStatus::from($result->status->value));
    }

    public function wave(LegacyMigrationWaveReadResult $result): LegacyMigrationOwnerReaderResult
    {
        return new LegacyMigrationOwnerReaderResult(LegacyMigrationOwnerReaderStatus::from($result->status->value));
    }

    public function reconciliation(LegacyMigrationReconciliationReadResult $result): LegacyMigrationOwnerReaderResult
    {
        return new LegacyMigrationOwnerReaderResult(LegacyMigrationOwnerReaderStatus::from($result->status->value));
    }

    public function quarantine(LegacyMigrationQuarantineReadResult $result): LegacyMigrationOwnerReaderResult
    {
        return new LegacyMigrationOwnerReaderResult(LegacyMigrationOwnerReaderStatus::from($result->status->value));
    }

    public function cutover(LegacyMigrationCutoverReadResult $result): LegacyMigrationOwnerReaderResult
    {
        return new LegacyMigrationOwnerReaderResult(LegacyMigrationOwnerReaderStatus::from($result->status->value));
    }
}
