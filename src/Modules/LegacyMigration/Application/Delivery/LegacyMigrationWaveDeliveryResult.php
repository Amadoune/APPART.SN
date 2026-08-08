<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

final readonly class LegacyMigrationWaveDeliveryResult
{
    public function __construct(public LegacyMigrationWaveDeliveryV1 $delivery) {}

    public function status(): LegacyMigrationWaveDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
