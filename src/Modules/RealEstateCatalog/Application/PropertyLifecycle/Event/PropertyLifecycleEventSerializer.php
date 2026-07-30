<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

final readonly class PropertyLifecycleEventSerializer
{
    public function serialize(PropertyLifecycleEvent $event): string
    {
        return json_encode($event->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
