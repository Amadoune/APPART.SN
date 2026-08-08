<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class SecretInventoryEventPayload
{
    public function __construct(public SecretInventoryEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
