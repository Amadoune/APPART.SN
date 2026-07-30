<?php

namespace Appart\Modules\SearchDiscovery\Domain\Event;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

abstract readonly class AbstractSearchIndexEvent implements SearchIndexEvent
{
    private SearchIndexEventMetadata $metadata;

    public function __construct(private SearchIndexId $indexId, private SearchDocumentId $documentId, private ListingId $listingId, private DateTimeImmutable $occurredAt)
    {
        $this->metadata = new SearchIndexEventMetadata;
    }

    public function indexId(): SearchIndexId
    {
        return $this->indexId;
    }

    public function documentId(): SearchDocumentId
    {
        return $this->documentId;
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
