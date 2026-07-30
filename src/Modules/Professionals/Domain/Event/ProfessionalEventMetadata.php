<?php

namespace Appart\Modules\Professionals\Domain\Event;

final class ProfessionalEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
