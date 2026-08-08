<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class ComplianceControlEventPayload
{
    public function __construct(public ComplianceControlEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
