<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

final readonly class LegacyMigrationWaveEventV1
{
    public function __construct(public LegacyMigrationWaveEventType $type, public LegacyMigrationWaveEventPayload $payload) {}
}
