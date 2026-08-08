<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

final readonly class LegacyMigrationInventoryResultV1
{
    public string $observedAt;

    public function __construct(public LegacyMigrationInventoryStatusV1 $status, LegacyMigrationObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
