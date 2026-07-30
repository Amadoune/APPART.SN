<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Event;

final class PropertyEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
