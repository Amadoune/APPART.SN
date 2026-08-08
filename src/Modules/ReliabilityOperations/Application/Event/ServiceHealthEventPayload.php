<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class ServiceHealthEventPayload
{
    public function __construct(public ServiceHealthEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
