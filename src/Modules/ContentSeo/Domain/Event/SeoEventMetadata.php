<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

final class SeoEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
