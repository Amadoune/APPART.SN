<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;

final readonly class LegacyMigrationReconciliationReadResult
{
    private function __construct(public LegacyMigrationReconciliationStatusV1 $status, public ?LegacyMigrationReconciliationRevisionState $revision) {}

    public static function found(LegacyMigrationReconciliationRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(LegacyMigrationReconciliationStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(LegacyMigrationReconciliationStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LegacyMigrationReconciliationStatusV1::DependencyUnavailable, null);
    }
}
