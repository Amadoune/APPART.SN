<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventType;

final readonly class LegacyMigrationInventoryDeliveryV1
{
    public function __construct(public LegacyMigrationInventoryEventType $type, public LegacyMigrationInventoryDeliveryPayload $payload) {}
}
