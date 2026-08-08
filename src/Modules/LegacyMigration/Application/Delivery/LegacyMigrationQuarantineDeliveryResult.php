<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

final readonly class LegacyMigrationQuarantineDeliveryResult
{
    public function __construct(public LegacyMigrationQuarantineDeliveryV1 $delivery) {}

    public function status(): LegacyMigrationQuarantineDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
