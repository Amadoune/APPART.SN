<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

final readonly class LegacyMigrationCutoverEventV1
{
    public function __construct(public LegacyMigrationCutoverEventType $type, public LegacyMigrationCutoverEventPayload $payload) {}
}
