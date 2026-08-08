<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventType;

final readonly class OperationalSeoDeliveryPayload
{
    public function __construct(public OperationalSeoEventType $type, public OperationalSeoDeliveryStatus $status, public string $observedAt) {}

    /** @return array{type:string, status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['type' => $this->type->value, 'status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
