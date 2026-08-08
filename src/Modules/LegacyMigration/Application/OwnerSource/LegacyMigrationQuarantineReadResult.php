<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;

final readonly class LegacyMigrationQuarantineReadResult
{
    private function __construct(public LegacyMigrationQuarantineStatusV1 $status, public ?LegacyMigrationQuarantineRevisionState $revision) {}

    public static function found(LegacyMigrationQuarantineRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(LegacyMigrationQuarantineStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(LegacyMigrationQuarantineStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LegacyMigrationQuarantineStatusV1::DependencyUnavailable, null);
    }
}
