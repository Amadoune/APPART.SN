<?php

namespace Appart\Modules\Notifications\Application\Event;

final readonly class NotificationEventPayload
{
    public function __construct(public NotificationEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
