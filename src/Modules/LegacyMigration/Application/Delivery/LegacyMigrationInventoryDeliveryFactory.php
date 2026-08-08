<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventV1;

final readonly class LegacyMigrationInventoryDeliveryFactory
{
    public function create(LegacyMigrationInventoryEventV1 $event): LegacyMigrationInventoryDeliveryResult
    {
        return new LegacyMigrationInventoryDeliveryResult(new LegacyMigrationInventoryDeliveryV1($event->type, new LegacyMigrationInventoryDeliveryPayload(LegacyMigrationInventoryDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
