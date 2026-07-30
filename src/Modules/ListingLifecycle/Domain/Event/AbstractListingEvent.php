<?php

namespace Appart\Modules\ListingLifecycle\Domain\Event;

use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;

abstract readonly class AbstractListingEvent implements ListingEvent
{
    private ListingEventMetadata $metadata;

    public function __construct(private ListingId $listingId, private ListingRevisionId $revisionId, private ?ListingStatus $previousStatus, private TransitionEvidence $evidence)
    {
        $this->metadata = new ListingEventMetadata;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function revisionId(): ListingRevisionId
    {
        return $this->revisionId;
    }

    public function previousStatus(): ?ListingStatus
    {
        return $this->previousStatus;
    }

    public function actorId(): ActorId
    {
        return $this->evidence->actorId;
    }

    public function trigger(): TransitionTrigger
    {
        return $this->evidence->trigger;
    }

    public function reason(): TransitionReason
    {
        return $this->evidence->reason;
    }

    public function origin(): TransitionOrigin
    {
        return $this->evidence->origin;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->evidence->occurredAt;
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
