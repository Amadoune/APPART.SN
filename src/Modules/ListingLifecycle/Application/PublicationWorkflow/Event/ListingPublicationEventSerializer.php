<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

final readonly class ListingPublicationEventSerializer
{
    public function serialize(ListingPublicationEvent $event): string
    {
        return json_encode($event->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
