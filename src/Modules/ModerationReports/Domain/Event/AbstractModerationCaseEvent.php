<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use DateTimeImmutable;

abstract readonly class AbstractModerationCaseEvent implements ModerationCaseEvent
{
    private ModerationCaseEventMetadata $metadata;

    public function __construct(private ModerationCaseId $caseId, private ListingId $listingId, private DateTimeImmutable $occurredAt)
    {
        $this->metadata = new ModerationCaseEventMetadata;
    }

    public function caseId(): ModerationCaseId
    {
        return $this->caseId;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateVersion(): int
    {
        return $this->metadata->aggregateVersion;
    }

    public function eventIndex(): int
    {
        return $this->metadata->eventIndex;
    }

    final public function stamp(int $version, int $index): void
    {
        $this->metadata->aggregateVersion = $version;
        $this->metadata->eventIndex = $index;
    }
}
