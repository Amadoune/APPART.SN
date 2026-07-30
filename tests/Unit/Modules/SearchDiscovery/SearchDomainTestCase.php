<?php

namespace Tests\Unit\Modules\SearchDiscovery;

use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchFacet;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFacetPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchVisibilityPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetKey;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class SearchDomainTestCase extends TestCase
{
    protected function listingId(int $suffix = 1): ListingId
    {
        return ListingId::fromString(sprintf('70000000-0000-4000-8000-%012d', $suffix));
    }

    protected function sources(int $version, ListingSearchState $listingState = ListingSearchState::Published, PropertySearchState $propertyState = PropertySearchState::Available, MediaSearchState $mediaState = MediaSearchState::Ready, ?ListingId $id = null): array
    {
        $id ??= $this->listingId();

        return [
            new ListingProjectionSource($id, $listingState, SearchRank::fromInt(100), [$this->facet('category', 'apartment', SourceKind::Listing)], $this->revision(SourceKind::Listing, $version)),
            new PropertyProjectionSource($id, $propertyState, [$this->facet('city', 'dakar', SourceKind::Property)], $this->revision(SourceKind::Property, $version)),
            new MediaProjectionSource($id, $mediaState, [$this->facet('has_image', 'yes', SourceKind::Media)], $this->revision(SourceKind::Media, $version)),
        ];
    }

    protected function projection(array $sources): SearchProjection
    {
        return (new SearchProjectionPolicy(new SearchVisibilityPolicy, new SearchFacetPolicy))->build(...$sources);
    }

    protected function facet(string $key, string $value, SourceKind $source): SearchFacet
    {
        return new SearchFacet(SearchFacetKey::fromString($key), SearchFacetValue::fromString($value), $source);
    }

    protected function revision(SourceKind $source, int $version): SourceRevision
    {
        return SourceRevision::create($source, $version, sprintf('90000000-0000-4000-8000-%012d', ($version * 10) + array_search($source, SourceKind::cases(), true)), $this->at($version));
    }

    protected function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-17T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}
