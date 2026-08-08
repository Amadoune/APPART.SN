<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventV1;

final readonly class LegacyMigrationQuarantineDeliveryFactory
{
    public function create(LegacyMigrationQuarantineEventV1 $event): LegacyMigrationQuarantineDeliveryResult
    {
        return new LegacyMigrationQuarantineDeliveryResult(new LegacyMigrationQuarantineDeliveryV1($event->type, new LegacyMigrationQuarantineDeliveryPayload(LegacyMigrationQuarantineDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
