<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

final readonly class AdministrationQueueEventPayload
{
    public function __construct(public AdministrationQueueEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
