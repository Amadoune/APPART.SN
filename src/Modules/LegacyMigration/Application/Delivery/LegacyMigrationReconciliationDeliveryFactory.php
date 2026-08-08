<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventV1;

final readonly class LegacyMigrationReconciliationDeliveryFactory
{
    public function create(LegacyMigrationReconciliationEventV1 $event): LegacyMigrationReconciliationDeliveryResult
    {
        return new LegacyMigrationReconciliationDeliveryResult(new LegacyMigrationReconciliationDeliveryV1($event->type, new LegacyMigrationReconciliationDeliveryPayload(LegacyMigrationReconciliationDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
