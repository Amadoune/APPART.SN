<?php

namespace Appart\Modules\ListingLifecycle\Domain\Event;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;

interface ListingEvent
{
    public function listingId(): ListingId;

    public function revisionId(): ListingRevisionId;

    public function previousStatus(): ?ListingStatus;

    public function actorId(): ActorId;

    public function trigger(): TransitionTrigger;

    public function reason(): TransitionReason;

    public function origin(): TransitionOrigin;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
