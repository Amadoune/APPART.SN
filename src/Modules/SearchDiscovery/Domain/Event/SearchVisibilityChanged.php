<?php

namespace Appart\Modules\SearchDiscovery\Domain\Event;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

final readonly class SearchVisibilityChanged extends AbstractSearchIndexEvent
{
    public function __construct(SearchIndexId $index, SearchDocumentId $document, ListingId $listing, public ProjectionState $previous, public ProjectionState $current, DateTimeImmutable $at)
    {
        parent::__construct($index, $document, $listing, $at);
    }
}
