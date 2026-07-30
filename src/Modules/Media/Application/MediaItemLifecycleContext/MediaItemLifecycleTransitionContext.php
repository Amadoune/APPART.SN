<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;

final readonly class MediaItemLifecycleTransitionContext
{
    public function __construct(
        public MediaItemLifecycleContextVersion $contractVersion,
        public MediaCollectionId $collectionId,
        public MediaId $mediaId,
        public MediaItemLifecycleExpectedVersion $expectedVersion,
        public MediaCollectionDecisionVersion $collectionVersion,
        public MediaItemLifecycleActorId $actor,
        public MediaItemLifecycleOccurredAt $occurredAt,
        public MediaCollectionTransitionDecision $collectionDecision,
    ) {}

    public function checksum(): MediaItemLifecycleContextChecksum
    {
        return MediaItemLifecycleContextChecksum::fromString(hash('sha256', implode("\n", [
            (string) $this->contractVersion->value,
            $this->collectionId->value,
            $this->mediaId->value,
            (string) $this->expectedVersion->value,
            (string) $this->collectionVersion->value,
            $this->actor->value,
            $this->occurredAt->canonical(),
            $this->collectionDecision->disposition->value,
            $this->collectionDecision->replacementMediaId->value ?? '',
        ])));
    }
}
