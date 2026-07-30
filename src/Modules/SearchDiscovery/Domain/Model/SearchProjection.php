<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;

final readonly class SearchProjection
{
    /** @param list<SearchFacet> $facets */
    private function __construct(
        public ProjectionState $state,
        public SearchRank $rank,
        public array $facets,
        public SourceRevisionSet $revisions,
    ) {}

    /** @param list<SearchFacet> $facets */
    public static function derived(ProjectionState $state, SearchRank $rank, array $facets, SourceRevisionSet $revisions): self
    {
        return new self($state, $rank, $facets, $revisions);
    }
}
