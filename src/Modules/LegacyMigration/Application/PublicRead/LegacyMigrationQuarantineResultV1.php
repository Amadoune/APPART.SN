<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

final readonly class LegacyMigrationQuarantineResultV1
{
    public string $observedAt;

    public function __construct(public LegacyMigrationQuarantineStatusV1 $status, LegacyMigrationObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
