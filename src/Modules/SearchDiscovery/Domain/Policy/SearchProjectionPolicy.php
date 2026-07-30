<?php

namespace Appart\Modules\SearchDiscovery\Domain\Policy;

use Appart\Modules\SearchDiscovery\Domain\Exception\InconsistentProjectionSources;
use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;

final readonly class SearchProjectionPolicy
{
    public function __construct(
        private SearchVisibilityPolicy $visibility,
        private SearchFacetPolicy $facets,
    ) {}

    public function build(ListingProjectionSource $listing, PropertyProjectionSource $property, MediaProjectionSource $media): SearchProjection
    {
        if (! $listing->listingId->equals($property->listingId) || ! $listing->listingId->equals($media->listingId)) {
            throw new InconsistentProjectionSources;
        }

        return SearchProjection::derived(
            $this->visibility->derive($listing, $property, $media),
            $listing->rank,
            $this->facets->govern([...$listing->facets, ...$property->facets, ...$media->facets]),
            new SourceRevisionSet($listing->revision, $property->revision, $media->revision),
        );
    }
}
