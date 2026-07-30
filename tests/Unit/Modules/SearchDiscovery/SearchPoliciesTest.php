<?php

namespace Tests\Unit\Modules\SearchDiscovery;

use Appart\Modules\SearchDiscovery\Domain\Exception\ForbiddenSearchFacet;
use Appart\Modules\SearchDiscovery\Domain\Exception\InconsistentProjectionSources;
use Appart\Modules\SearchDiscovery\Domain\Exception\SearchViolation;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFacetPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;

final class SearchPoliciesTest extends SearchDomainTestCase
{
    public function test_visibility_is_fully_derived_as_visible(): void
    {
        self::assertSame(ProjectionState::Visible, $this->projection($this->sources(1))->state);
    }

    public function test_non_published_listing_is_hidden(): void
    {
        self::assertSame(ProjectionState::Hidden, $this->projection($this->sources(1, ListingSearchState::TemporarilyUnavailable))->state);
    }

    public function test_unavailable_property_or_media_causes_prudent_withdrawal(): void
    {
        self::assertSame(ProjectionState::Hidden, $this->projection($this->sources(1, propertyState: PropertySearchState::Unavailable))->state);
        self::assertSame(ProjectionState::Hidden, $this->projection($this->sources(1, mediaState: MediaSearchState::MissingPrimary))->state);
    }

    public function test_terminal_listing_or_archived_property_is_removed(): void
    {
        self::assertSame(ProjectionState::Removed, $this->projection($this->sources(1, ListingSearchState::Terminal))->state);
        self::assertSame(ProjectionState::Removed, $this->projection($this->sources(1, propertyState: PropertySearchState::Archived))->state);
    }

    public function test_multivalued_facets_keep_values_and_provenance(): void
    {
        $policy = new SearchFacetPolicy;
        $facets = $policy->govern([
            $this->facet('amenity', 'pool', SourceKind::Property),
            $this->facet('amenity', 'parking', SourceKind::Property),
            $this->facet('amenity', 'parking', SourceKind::Listing),
        ]);

        self::assertCount(3, $facets);
        self::assertSame(SourceKind::Listing, $facets[0]->source);
    }

    public function test_private_or_unknown_facet_is_forbidden(): void
    {
        $this->expectException(ForbiddenSearchFacet::class);
        (new SearchFacetPolicy)->govern([$this->facet('owner_email', 'private@example.test', SourceKind::Listing)]);
    }

    public function test_conflicting_single_value_facet_is_rejected(): void
    {
        $this->expectException(SearchViolation::class);
        (new SearchFacetPolicy)->govern([
            $this->facet('city', 'dakar', SourceKind::Property),
            $this->facet('city', 'thies', SourceKind::Listing),
        ]);
    }

    public function test_sources_for_different_listings_are_rejected(): void
    {
        [$listing, $property, $media] = $this->sources(1);
        $property = new PropertyProjectionSource($this->listingId(2), $property->state, $property->facets, $property->revision);
        $media = new MediaProjectionSource($media->listingId, $media->state, $media->facets, $media->revision);

        $this->expectException(InconsistentProjectionSources::class);
        $this->projection([$listing, $property, $media]);
    }
}
