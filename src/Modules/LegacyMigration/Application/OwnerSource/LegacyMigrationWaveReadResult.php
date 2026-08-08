<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;

final readonly class LegacyMigrationWaveReadResult
{
    private function __construct(public LegacyMigrationWaveStatusV1 $status, public ?LegacyMigrationWaveRevisionState $revision) {}

    public static function found(LegacyMigrationWaveRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(LegacyMigrationWaveStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(LegacyMigrationWaveStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LegacyMigrationWaveStatusV1::DependencyUnavailable, null);
    }
}
