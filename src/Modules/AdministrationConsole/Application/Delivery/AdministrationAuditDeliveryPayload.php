<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

final readonly class AdministrationAuditDeliveryPayload
{
    public function __construct(public AdministrationAuditDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
