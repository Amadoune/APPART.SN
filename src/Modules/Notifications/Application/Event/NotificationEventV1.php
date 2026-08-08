<?php

namespace Appart\Modules\Notifications\Application\Event;

final readonly class NotificationEventV1
{
    public function __construct(public NotificationEventType $type, public NotificationEventPayload $payload) {}
}
