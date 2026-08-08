<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;

final readonly class LegacyMigrationInventoryReadResult
{
    private function __construct(public LegacyMigrationInventoryStatusV1 $status, public ?LegacyMigrationInventoryRevisionState $revision) {}

    public static function found(LegacyMigrationInventoryRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(LegacyMigrationInventoryStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(LegacyMigrationInventoryStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LegacyMigrationInventoryStatusV1::DependencyUnavailable, null);
    }
}
