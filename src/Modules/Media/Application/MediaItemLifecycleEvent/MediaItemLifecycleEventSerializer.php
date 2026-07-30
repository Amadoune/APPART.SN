<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

final readonly class MediaItemLifecycleEventSerializer
{
    public function serialize(MediaItemLifecycleEvent $event): string
    {
        return json_encode(
            $event->canonical(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
}
