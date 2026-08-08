<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventV1;

final readonly class LegacyMigrationWaveDeliveryFactory
{
    public function create(LegacyMigrationWaveEventV1 $event): LegacyMigrationWaveDeliveryResult
    {
        return new LegacyMigrationWaveDeliveryResult(new LegacyMigrationWaveDeliveryV1($event->type, new LegacyMigrationWaveDeliveryPayload(LegacyMigrationWaveDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
