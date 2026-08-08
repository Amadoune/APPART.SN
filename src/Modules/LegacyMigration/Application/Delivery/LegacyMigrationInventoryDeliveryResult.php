<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

final readonly class LegacyMigrationInventoryDeliveryResult
{
    public function __construct(public LegacyMigrationInventoryDeliveryV1 $delivery) {}

    public function status(): LegacyMigrationInventoryDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
