<?php

namespace Appart\Modules\Media\Domain\Event;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use DateTimeImmutable;

abstract readonly class AbstractMediaCollectionEvent implements MediaCollectionEvent
{
    private MediaCollectionEventMetadata $metadata;

    public function __construct(private MediaCollectionId $id, private DateTimeImmutable $at)
    {
        $this->metadata = new MediaCollectionEventMetadata;
    }

    public function collectionId(): MediaCollectionId
    {
        return $this->id;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->at;
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
