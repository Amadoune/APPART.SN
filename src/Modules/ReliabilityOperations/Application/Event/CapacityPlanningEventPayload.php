<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class CapacityPlanningEventPayload
{
    public function __construct(public CapacityPlanningEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
