<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

final readonly class AdministrationQueueEventV1
{
    public function __construct(public AdministrationQueueEventType $type, public AdministrationQueueEventPayload $payload) {}
}
