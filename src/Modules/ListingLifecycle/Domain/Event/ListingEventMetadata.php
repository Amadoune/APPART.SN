<?php

namespace Appart\Modules\ListingLifecycle\Domain\Event;

final class ListingEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
