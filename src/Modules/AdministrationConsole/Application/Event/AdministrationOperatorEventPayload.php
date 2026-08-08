<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

final readonly class AdministrationOperatorEventPayload
{
    public function __construct(public AdministrationOperatorEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
