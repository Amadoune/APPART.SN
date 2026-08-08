<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

final readonly class LegacyMigrationReconciliationEventV1
{
    public function __construct(public LegacyMigrationReconciliationEventType $type, public LegacyMigrationReconciliationEventPayload $payload) {}
}
