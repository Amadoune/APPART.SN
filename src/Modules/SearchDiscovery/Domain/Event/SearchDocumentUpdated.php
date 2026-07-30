<?php

namespace Appart\Modules\SearchDiscovery\Domain\Event;

use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionChange;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

final readonly class SearchDocumentUpdated extends AbstractSearchIndexEvent
{
    public function __construct(SearchIndexId $index, SearchDocumentId $document, ListingId $listing, public SearchProjection $projection, public ProjectionChange $change, DateTimeImmutable $at)
    {
        parent::__construct($index, $document, $listing, $at);
    }
}
