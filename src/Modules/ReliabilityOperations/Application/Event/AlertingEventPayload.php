<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class AlertingEventPayload
{
    public function __construct(public AlertingEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
