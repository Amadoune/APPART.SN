<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventType;

final readonly class LegacyMigrationQuarantineDeliveryV1
{
    public function __construct(public LegacyMigrationQuarantineEventType $type, public LegacyMigrationQuarantineDeliveryPayload $payload) {}
}
