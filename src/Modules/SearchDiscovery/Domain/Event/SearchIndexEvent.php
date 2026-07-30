<?php

namespace Appart\Modules\SearchDiscovery\Domain\Event;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

interface SearchIndexEvent
{
    public function indexId(): SearchIndexId;

    public function documentId(): SearchDocumentId;

    public function listingId(): ListingId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
