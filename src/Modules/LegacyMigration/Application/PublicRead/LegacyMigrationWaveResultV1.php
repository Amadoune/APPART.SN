<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

final readonly class LegacyMigrationWaveResultV1
{
    public string $observedAt;

    public function __construct(public LegacyMigrationWaveStatusV1 $status, LegacyMigrationObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
