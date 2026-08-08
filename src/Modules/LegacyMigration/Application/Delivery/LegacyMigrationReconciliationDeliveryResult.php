<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

final readonly class LegacyMigrationReconciliationDeliveryResult
{
    public function __construct(public LegacyMigrationReconciliationDeliveryV1 $delivery) {}

    public function status(): LegacyMigrationReconciliationDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
