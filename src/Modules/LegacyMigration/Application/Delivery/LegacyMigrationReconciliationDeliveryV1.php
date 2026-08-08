<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventType;

final readonly class LegacyMigrationReconciliationDeliveryV1
{
    public function __construct(public LegacyMigrationReconciliationEventType $type, public LegacyMigrationReconciliationDeliveryPayload $payload) {}
}
