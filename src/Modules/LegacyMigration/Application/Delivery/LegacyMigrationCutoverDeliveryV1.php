<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventType;

final readonly class LegacyMigrationCutoverDeliveryV1
{
    public function __construct(public LegacyMigrationCutoverEventType $type, public LegacyMigrationCutoverDeliveryPayload $payload) {}
}
