<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

final readonly class AdministrationOperatorDeliveryPayload
{
    public function __construct(public AdministrationOperatorDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
