<?php

namespace Appart\Modules\Media\Domain\Event;

final class MediaCollectionEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
