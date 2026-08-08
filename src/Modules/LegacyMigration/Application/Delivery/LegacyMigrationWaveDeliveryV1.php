<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventType;

final readonly class LegacyMigrationWaveDeliveryV1
{
    public function __construct(public LegacyMigrationWaveEventType $type, public LegacyMigrationWaveDeliveryPayload $payload) {}
}
