<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

final readonly class LegacyMigrationWaveEventPayload
{
    public function __construct(public LegacyMigrationWaveEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
