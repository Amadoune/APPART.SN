<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;

final readonly class LegacyMigrationCutoverReadResult
{
    private function __construct(public LegacyMigrationCutoverStatusV1 $status, public ?LegacyMigrationCutoverRevisionState $revision) {}

    public static function found(LegacyMigrationCutoverRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(LegacyMigrationCutoverStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(LegacyMigrationCutoverStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LegacyMigrationCutoverStatusV1::DependencyUnavailable, null);
    }
}
