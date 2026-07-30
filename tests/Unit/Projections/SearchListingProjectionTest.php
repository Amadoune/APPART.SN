<?php

namespace Tests\Unit\Projections;

use App\Projections\SearchListingProjection;
use App\Projections\SearchListingProjectionBuilder;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId as ListingPropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SearchListingProjectionTest extends TestCase
{
    private SearchListingProjectionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new SearchListingProjectionBuilder;
    }

    public function test_builds_a_complete_immutable_projection(): void
    {
        $projection = $this->builder->build($this->property(), $this->media(), $this->publishedListing());

        self::assertInstanceOf(SearchListingProjection::class, $projection);
        self::assertTrue((new \ReflectionClass($projection))->isReadOnly());
        self::assertSame('71000000-0000-4000-8000-000000000001', $projection->listingId);
        self::assertSame($this->propertyId(), $projection->propertyId);
        self::assertSame('72000000-0000-4000-8000-000000000001', $projection->mediaCollectionId);
        self::assertSame('published', $projection->listingStatus);
        self::assertSame('place:dakar:plateau', $projection->geographicPlaceId);
        self::assertSame('apartment', $projection->propertyType);
        self::assertSame(120, $projection->surfaceSquareMeters);
        self::assertSame(5, $projection->roomCount);
        self::assertSame('72000000-0000-4000-8000-000000000101', $projection->primaryMediaId);
        self::assertEquals($this->at(3), $projection->publishedAt);
        self::assertEquals($this->at(120), $projection->expiresAt);
    }

    public function test_reconstruction_from_the_same_aggregates_is_identical(): void
    {
        $first = $this->builder->build($this->property(), $this->media(), $this->publishedListing());
        $second = $this->builder->build($this->property(), $this->media(), $this->publishedListing());

        self::assertEquals($first, $second);
    }

    public function test_non_published_listing_has_no_public_search_projection(): void
    {
        self::assertNull($this->builder->build($this->property(), $this->media(), $this->draftListing()));
    }

    public function test_archived_property_has_no_public_search_projection(): void
    {
        $property = $this->property();
        $property->archive($this->at(4));

        self::assertNull($this->builder->build($property, $this->media(), $this->publishedListing()));
    }

    public function test_missing_primary_media_has_no_public_search_projection(): void
    {
        self::assertNull($this->builder->build($this->property(), $this->emptyMedia(), $this->publishedListing()));
    }

    public function test_derived_values_come_only_from_source_aggregates(): void
    {
        $property = $this->property();
        $media = $this->media();
        $listing = $this->publishedListing();
        $projection = $this->builder->build($property, $media, $listing);

        self::assertSame($property->address()?->placeId->value, $projection?->geographicPlaceId);
        self::assertSame($property->surface()?->squareMeters, $projection?->surfaceSquareMeters);
        self::assertSame($media->primary()?->id->value, $projection?->primaryMediaId);
        self::assertSame($listing->expirationDate()?->value, $projection?->expiresAt);
        self::assertSame($listing->revisions()[3]->occurredAt, $projection?->publishedAt);
    }

    private function property(): Property
    {
        return Property::register(
            PropertyId::fromString($this->propertyId()),
            PropertyReference::fromString('SEARCH-PROPERTY-001'),
            PropertyType::Apartment,
            SurfaceArea::fromSquareMeters(120),
            RoomCount::fromInt(5),
            BathroomCount::fromInt(2),
            ConstructionYear::fromInt(2020),
            new Address(AddressId::fromString('70000000-0000-4000-8000-000000000010'), GeographicPlaceId::fromString('place:dakar:plateau'), AddressLine::fromString('Adresse non projetee')),
            BusinessYear::fromInt(2026),
            new PropertyTypePolicy,
            $this->at(0),
        );
    }

    private function media(): MediaCollection
    {
        $media = $this->emptyMedia();
        $media->add(MediaId::fromString('72000000-0000-4000-8000-000000000101'), MediaType::Image, MediaChecksum::fromSha256(str_repeat('a', 64)), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->at(1));

        return $media;
    }

    private function emptyMedia(): MediaCollection
    {
        return MediaCollection::create(MediaCollectionId::fromString('72000000-0000-4000-8000-000000000001'), MediaPropertyId::fromString($this->propertyId()), $this->at(0));
    }

    private function publishedListing(): Listing
    {
        $listing = $this->draftListing();
        $policy = new ListingTransitionPolicy;
        $listing->submit($this->revision(2), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1), $policy, PropertyAvailability::Eligible);
        $listing->sendToReview($this->revision(3), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2), $policy, PropertyAvailability::Eligible);
        $listing->publish($this->revision(4), ExpirationDate::fromDateTime($this->at(120)), $this->evidence(TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, 3), $policy, PropertyAvailability::Eligible);

        return $listing;
    }

    private function draftListing(): Listing
    {
        return Listing::createDraft(ListingId::fromString('71000000-0000-4000-8000-000000000001'), ListingPropertyId::fromString($this->propertyId()), $this->revision(1), $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0), new ListingTransitionPolicy, PropertyAvailability::Eligible);
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin, int $minute): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor:search-projection'), $trigger, TransitionReason::fromString('Projection fixture transition'), $origin, $this->at($minute));
    }

    private function revision(int $suffix): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('71000000-0000-4000-8000-%012d', $suffix));
    }

    private function propertyId(): string
    {
        return '70000000-0000-4000-8000-000000000001';
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-18T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}
