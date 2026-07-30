<?php

namespace Appart\Modules\ContentSeo\Domain\Event;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

abstract readonly class AbstractSeoEvent implements SeoEvent
{
    private SeoEventMetadata $metadata;

    public function __construct(private SeoProjectionId $projectionId, private ListingId $listingId, private DateTimeImmutable $occurredAt)
    {
        $this->metadata = new SeoEventMetadata;
    }

    public function projectionId(): SeoProjectionId
    {
        return $this->projectionId;
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
