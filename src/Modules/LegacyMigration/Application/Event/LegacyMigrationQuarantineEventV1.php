<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

final readonly class LegacyMigrationQuarantineEventV1
{
    public function __construct(public LegacyMigrationQuarantineEventType $type, public LegacyMigrationQuarantineEventPayload $payload) {}
}
