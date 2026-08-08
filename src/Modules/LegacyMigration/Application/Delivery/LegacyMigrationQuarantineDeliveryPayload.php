<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

final readonly class LegacyMigrationQuarantineDeliveryPayload
{
    public function __construct(public LegacyMigrationQuarantineDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
