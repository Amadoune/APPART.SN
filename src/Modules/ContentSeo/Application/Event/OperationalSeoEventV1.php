<?php

namespace Appart\Modules\ContentSeo\Application\Event;

final readonly class OperationalSeoEventV1
{
    public function __construct(public OperationalSeoEventType $type, public OperationalSeoEventPayload $payload) {}
}
