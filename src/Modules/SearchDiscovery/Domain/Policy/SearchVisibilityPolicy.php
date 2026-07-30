<?php

namespace Appart\Modules\SearchDiscovery\Domain\Policy;

use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;

final readonly class SearchVisibilityPolicy
{
    public function derive(ListingProjectionSource $listing, PropertyProjectionSource $property, MediaProjectionSource $media): ProjectionState
    {
        if ($listing->state === ListingSearchState::Terminal || $property->state === PropertySearchState::Archived) {
            return ProjectionState::Removed;
        }

        if ($listing->state !== ListingSearchState::Published || $property->state !== PropertySearchState::Available || $media->state !== MediaSearchState::Ready) {
            return ProjectionState::Hidden;
        }

        return ProjectionState::Visible;
    }
}
