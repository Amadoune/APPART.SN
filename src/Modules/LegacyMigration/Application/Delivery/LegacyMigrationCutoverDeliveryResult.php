<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

final readonly class LegacyMigrationCutoverDeliveryResult
{
    public function __construct(public LegacyMigrationCutoverDeliveryV1 $delivery) {}

    public function status(): LegacyMigrationCutoverDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
