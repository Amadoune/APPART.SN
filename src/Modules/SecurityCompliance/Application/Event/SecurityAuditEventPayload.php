<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class SecurityAuditEventPayload
{
    public function __construct(public SecurityAuditEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
