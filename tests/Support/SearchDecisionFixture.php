<?php

namespace Tests\Support;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchFacet;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetKey;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;
use DateTimeImmutable;

final class SearchDecisionFixture
{
    public static function make(int $version = 1, int $rank = 500, string $listingId = '99200000-0000-4000-8000-000000000001'): SearchDecision
    {
        $at = new DateTimeImmutable('2026-07-19T10:00:00+00:00');
        $revisions = new SourceRevisionSet(
            SourceRevision::create(SourceKind::Listing, $version, '99200000-0000-4000-8000-000000000011', $at),
            SourceRevision::create(SourceKind::Property, $version, '99200000-0000-4000-8000-000000000012', $at),
            SourceRevision::create(SourceKind::Media, $version, '99200000-0000-4000-8000-000000000013', $at),
        );
        $projection = SearchProjection::derived(
            ProjectionState::Visible,
            SearchRank::fromInt($rank),
            [new SearchFacet(SearchFacetKey::fromString('property.type'), SearchFacetValue::fromString('apartment'), SourceKind::Property)],
            $revisions,
        );

        return new SearchDecision(SearchIndexId::fromString(sprintf('99300000-0000-4000-8000-%012d', $version)), ListingId::fromString($listingId), $version, $projection);
    }
}
