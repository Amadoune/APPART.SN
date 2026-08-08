<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

final readonly class LegacyMigrationReconciliationResultV1
{
    public string $observedAt;

    public function __construct(public LegacyMigrationReconciliationStatusV1 $status, LegacyMigrationObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
