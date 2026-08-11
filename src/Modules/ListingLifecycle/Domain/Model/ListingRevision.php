<?php

namespace Appart\Modules\ListingLifecycle\Domain\Model;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;

final readonly class ListingRevision
{
    public function __construct(
        public ListingRevisionId $id,
        public ?ListingStatus $previousStatus,
        public ListingStatus $status,
        public ActorId $actorId,
        public TransitionTrigger $trigger,
        public ?TransitionReason $reason,
        public TransitionOrigin $origin,
        public DateTimeImmutable $occurredAt,
    ) {}
}
