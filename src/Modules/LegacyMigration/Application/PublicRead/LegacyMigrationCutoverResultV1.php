<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

final readonly class LegacyMigrationCutoverResultV1
{
    public string $observedAt;

    public function __construct(public LegacyMigrationCutoverStatusV1 $status, LegacyMigrationObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
