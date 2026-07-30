<?php

namespace Appart\Modules\SearchDiscovery\Application\UseCase;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchIndexRegistry;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchIndex;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

final readonly class IndexListing
{
    public function __construct(
        private SearchIndexRegistry $indexes,
        private ProjectionSources $sources,
        private SearchProjectionPolicy $projections,
    ) {}

    public function execute(SearchIndexId $indexId, SearchDocumentId $documentId, ListingId $listingId, DateTimeImmutable $at): SearchIndex
    {
        [$listing, $property, $media] = $this->sources->load($listingId);
        $index = SearchIndex::create($indexId, $documentId, $listingId, $this->projections->build($listing, $property, $media), $at);
        $this->indexes->add($index);

        return $index;
    }
}
