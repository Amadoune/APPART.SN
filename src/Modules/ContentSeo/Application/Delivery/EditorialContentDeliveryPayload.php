<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventType;

final readonly class EditorialContentDeliveryPayload
{
    public function __construct(public EditorialContentEventType $type, public EditorialContentDeliveryStatus $status, public string $observedAt) {}

    /** @return array{type:string, status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['type' => $this->type->value, 'status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
