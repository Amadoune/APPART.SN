<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

final class AdministrativeActionEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
