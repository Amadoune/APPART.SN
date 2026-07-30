<?php

namespace Appart\Modules\SearchDiscovery\Domain\Event;

final class SearchIndexEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
