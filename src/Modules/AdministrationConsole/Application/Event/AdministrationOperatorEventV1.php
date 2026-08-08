<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

final readonly class AdministrationOperatorEventV1
{
    public function __construct(public AdministrationOperatorEventType $type, public AdministrationOperatorEventPayload $payload) {}
}
