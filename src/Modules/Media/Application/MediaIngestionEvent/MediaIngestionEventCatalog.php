<?php

namespace Appart\Modules\Media\Application\MediaIngestionEvent;

final class MediaIngestionEventCatalog
{
    /** @return list<MediaIngestionEventType> */
    public function events(): array
    {
        return MediaIngestionEventType::cases();
    }

    public function isPubliclyPublishable(MediaIngestionEventType $type): bool
    {
        return in_array($type, $this->events(), true);
    }
}
