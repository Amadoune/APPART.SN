<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class MaintenanceOperationsEventPayload
{
    public function __construct(public MaintenanceOperationsEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
