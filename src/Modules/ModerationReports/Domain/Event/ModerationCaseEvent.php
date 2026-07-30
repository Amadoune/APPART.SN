<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use DateTimeImmutable;

interface ModerationCaseEvent
{
    public function caseId(): ModerationCaseId;

    public function listingId(): ListingId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
