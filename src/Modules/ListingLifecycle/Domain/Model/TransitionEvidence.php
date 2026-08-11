<?php

namespace Appart\Modules\ListingLifecycle\Domain\Model;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;

final readonly class TransitionEvidence
{
    public function __construct(
        public ActorId $actorId,
        public TransitionTrigger $trigger,
        public ?TransitionReason $reason,
        public TransitionOrigin $origin,
        public DateTimeImmutable $occurredAt,
    ) {}
}
