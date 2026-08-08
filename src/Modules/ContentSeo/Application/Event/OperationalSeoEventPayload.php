<?php

namespace Appart\Modules\ContentSeo\Application\Event;

final readonly class OperationalSeoEventPayload
{
    public function __construct(public OperationalSeoEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
