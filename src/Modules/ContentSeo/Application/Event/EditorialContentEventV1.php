<?php

namespace Appart\Modules\ContentSeo\Application\Event;

final readonly class EditorialContentEventV1
{
    public function __construct(public EditorialContentEventType $type, public EditorialContentEventPayload $payload) {}
}
