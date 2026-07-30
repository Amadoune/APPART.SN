<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

final class ModerationCaseEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
