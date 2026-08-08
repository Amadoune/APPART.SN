<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

final readonly class LegacyMigrationInventoryEventV1
{
    public function __construct(public LegacyMigrationInventoryEventType $type, public LegacyMigrationInventoryEventPayload $payload) {}
}
