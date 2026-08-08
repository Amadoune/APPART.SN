<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventV1;

final readonly class LegacyMigrationCutoverDeliveryFactory
{
    public function create(LegacyMigrationCutoverEventV1 $event): LegacyMigrationCutoverDeliveryResult
    {
        return new LegacyMigrationCutoverDeliveryResult(new LegacyMigrationCutoverDeliveryV1($event->type, new LegacyMigrationCutoverDeliveryPayload(LegacyMigrationCutoverDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
