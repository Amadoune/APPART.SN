<?php

namespace Tests\Unit\PublicProjectionSourceLookup;

use App\Application\MultiTargetDelivery\MultiTargetPropagationSource;
use App\Application\MultiTargetDelivery\PagedMultiTargetPropagationStrategy;
use App\Application\ProjectionRuntimeSource\Contract\InspectablePublicListingProjectionSource;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyResult;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus;
use App\Application\PropertyListingResolution\Contract\PropertyListingsResolver;
use App\Application\PropertyListingResolution\PropertyListingsPage;
use App\Application\PropertyListingResolution\PropertyListingsPageStatus;
use App\Application\PublicGeographySource\PublicGeographyReadResult;
use App\Application\PublicMediaSource\PublicMediaReadResult;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionSourceLookup\Contract\MediaCollectionPropertyResolver;
use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyResolution;
use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyStatus;
use App\Application\PublicProjectionUpdater\PublicListingProjectionSources;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateResult;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolutionStatus;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolver;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionUpdaterConsumer;
use App\Infrastructure\PublicProjectionSourceLookup\CertifiedPublicProjectionSourceLookup;
use App\Infrastructure\PublicProjectionSourceLookup\RegistryMediaCollectionPropertyResolver;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\ProjectionRuntimeSource\CertifiedPublicListingProjectionSourceTest;

final class CertifiedPublicProjectionSourceLookupTest extends TestCase
{
    private const string LISTING = '99400000-0000-4000-8000-000000000001';

    private const string PROPERTY = '92000000-0000-4000-8000-000000000001';

    private const string MEDIA = '93000000-0000-4000-8000-000000000001';

    public function test_ready_listing_is_resolved_without_updater_or_reconstruction(): void
    {
        $lookup = new CertifiedPublicProjectionSourceLookup(CertifiedPublicListingProjectionSourceTest::scenario()->source(), $this->mediaOwnership(null));
        $resolution = $lookup->resolve($this->listingMessage());

        self::assertSame(PublicProjectionSourceResolutionStatus::Resolved, $resolution->status);
        self::assertSame(self::LISTING, $resolution->listingId);
    }

    public function test_missing_public_geography_and_media_are_readiness_results(): void
    {
        $scenario = CertifiedPublicListingProjectionSourceTest::scenario();
        $scenario->geography = PublicGeographyReadResult::missing('place:dakar:plateau');
        $lookup = new CertifiedPublicProjectionSourceLookup($scenario->source(), $this->mediaOwnership(null));
        self::assertSame(PublicProjectionSourceResolutionStatus::MissingPublicGeographyRevision, $lookup->resolve($this->listingMessage())->status);

        $scenario = CertifiedPublicListingProjectionSourceTest::scenario();
        $scenario->publicMedia = PublicMediaReadResult::missing(self::MEDIA);
        $lookup = new CertifiedPublicProjectionSourceLookup($scenario->source(), $this->mediaOwnership(null));
        self::assertSame(PublicProjectionSourceResolutionStatus::MissingPublicMediaRevision, $lookup->resolve($this->listingMessage())->status);
    }

    #[DataProvider('assemblyMappings')]
    public function test_listing_source_failures_are_classified_exhaustively(ProjectionSourceAssemblyStatus $assembly, PublicProjectionSourceResolutionStatus $expected): void
    {
        $lookup = new CertifiedPublicProjectionSourceLookup(new BlockedSource($assembly), $this->mediaOwnership(null));
        self::assertSame($expected, $lookup->resolve($this->listingMessage())->status);
    }

    public static function assemblyMappings(): iterable
    {
        yield [ProjectionSourceAssemblyStatus::InvalidListingIdentity, PublicProjectionSourceResolutionStatus::InvalidIdentity];
        yield [ProjectionSourceAssemblyStatus::ListingMissing, PublicProjectionSourceResolutionStatus::SourceUnavailable];
        yield [ProjectionSourceAssemblyStatus::SearchMissing, PublicProjectionSourceResolutionStatus::SourceUnavailable];
        yield [ProjectionSourceAssemblyStatus::ActiveGenerationMissing, PublicProjectionSourceResolutionStatus::SourceUnavailable];
        yield [ProjectionSourceAssemblyStatus::SearchCorrupted, PublicProjectionSourceResolutionStatus::Corrupted];
        yield [ProjectionSourceAssemblyStatus::DecisionTimeDivergent, PublicProjectionSourceResolutionStatus::Corrupted];
        yield [ProjectionSourceAssemblyStatus::ActiveGenerationCorrupted, PublicProjectionSourceResolutionStatus::Corrupted];
        yield [ProjectionSourceAssemblyStatus::MediaOwnershipAmbiguous, PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous];
    }

    public function test_property_is_normalized_without_preloading_any_listing(): void
    {
        $source = new CountingSource;
        $lookup = new CertifiedPublicProjectionSourceLookup($source, $this->mediaOwnership(null));
        $resolution = $lookup->resolve($this->propertyMessage(self::PROPERTY));

        self::assertSame(PublicProjectionSourceResolutionStatus::MultiTargetResolved, $resolution->status);
        self::assertSame(MultiTargetPropagationSource::Property, $resolution->multiTargetRequest?->source);
        self::assertSame(self::PROPERTY, $resolution->multiTargetRequest?->propertyId);
        self::assertSame(0, $source->calls);
    }

    public function test_invalid_property_is_explicit(): void
    {
        $lookup = new CertifiedPublicProjectionSourceLookup(new CountingSource, $this->mediaOwnership(null));
        self::assertSame(PublicProjectionSourceResolutionStatus::InvalidIdentity, $lookup->resolve($this->propertyMessage('invalid'))->status);
    }

    public function test_media_ownership_is_loaded_explicitly_and_normalized(): void
    {
        $collection = MediaCollection::create(MediaCollectionId::fromString(self::MEDIA), PropertyId::fromString(self::PROPERTY), new DateTimeImmutable('2026-07-19T10:00:00+00:00'));
        $lookup = new CertifiedPublicProjectionSourceLookup(new CountingSource, $this->mediaOwnership($collection));
        $resolution = $lookup->resolve($this->mediaMessage(self::MEDIA));

        self::assertSame(PublicProjectionSourceResolutionStatus::MultiTargetResolved, $resolution->status);
        self::assertSame(MultiTargetPropagationSource::Media, $resolution->multiTargetRequest?->source);
        self::assertSame(self::MEDIA, $resolution->multiTargetRequest?->sourceId);
        self::assertSame(self::PROPERTY, $resolution->multiTargetRequest?->propertyId);
    }

    public function test_missing_and_invalid_media_are_distinct(): void
    {
        $lookup = new CertifiedPublicProjectionSourceLookup(new CountingSource, $this->mediaOwnership(null));
        self::assertSame(PublicProjectionSourceResolutionStatus::MediaOwnershipMissing, $lookup->resolve($this->mediaMessage(self::MEDIA))->status);
        self::assertSame(PublicProjectionSourceResolutionStatus::InvalidIdentity, $lookup->resolve($this->mediaMessage('invalid'))->status);
    }

    public function test_ambiguous_and_corrupted_media_ownership_are_explicit(): void
    {
        $ambiguous = new CertifiedPublicProjectionSourceLookup(new CountingSource, new FixedMediaPropertyResolver(MediaCollectionPropertyStatus::Ambiguous));
        $corrupted = new CertifiedPublicProjectionSourceLookup(new CountingSource, new FixedMediaPropertyResolver(MediaCollectionPropertyStatus::Corrupted));

        self::assertSame(PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous, $ambiguous->resolve($this->mediaMessage(self::MEDIA))->status);
        self::assertSame(PublicProjectionSourceResolutionStatus::Corrupted, $corrupted->resolve($this->mediaMessage(self::MEDIA))->status);
    }

    public function test_concrete_lookup_is_consumed_directly_across_multiple_pages(): void
    {
        $lookup = new CertifiedPublicProjectionSourceLookup(new CountingSource, $this->mediaOwnership(null));
        $resolver = new PublicProjectionSourceResolver(new PublicProjectionDeliveryEventCatalog, $lookup);
        $targets = new LookupTargetResolver([
            new PropertyListingsPage(PropertyListingsPageStatus::Found, ['listing-1', 'listing-2'], 'next', false),
            new PropertyListingsPage(PropertyListingsPageStatus::Completed, ['listing-3'], null, true),
        ]);
        $updater = new LookupRecordingUpdater;
        $consumer = new PublicProjectionUpdaterConsumer($resolver, $updater, new PagedMultiTargetPropagationStrategy($targets), 2);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($this->propertyMessage(self::PROPERTY)));
        self::assertSame(['listing-1', 'listing-2', 'listing-3'], $updater->calls);
    }

    private function listingMessage(): PublicProjectionDeliveryMessage
    {
        return $this->message('listing.reconstruction.requested', 'ListingLifecycle', 'Listing', self::LISTING, new PublicProjectionDeliveryListingPayload(self::LISTING));
    }

    private function mediaOwnership(?MediaCollection $collection): RegistryMediaCollectionPropertyResolver
    {
        return new RegistryMediaCollectionPropertyResolver(new LookupMediaRegistry($collection));
    }

    private function propertyMessage(string $id): PublicProjectionDeliveryMessage
    {
        return $this->message('property.reconstruction.requested', 'RealEstateCatalog', 'Property', $id, new PublicProjectionDeliveryPropertyPayload($id));
    }

    private function mediaMessage(string $id): PublicProjectionDeliveryMessage
    {
        return $this->message('media.reconstruction.requested', 'Media', 'MediaCollection', $id, new PublicProjectionDeliveryMediaPayload($id));
    }

    private function message(string $type, string $module, string $aggregate, string $id, PublicProjectionDeliveryPayload $payload): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString($type), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString($module), PublicProjectionDeliveryAggregateType::fromString($aggregate), PublicProjectionDeliveryAggregateId::fromString($id), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), $payload);

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }
}

final class BlockedSource implements InspectablePublicListingProjectionSource
{
    public function __construct(private ProjectionSourceAssemblyStatus $status) {}

    public function inspect(string $listingId): ProjectionSourceAssemblyResult
    {
        return ProjectionSourceAssemblyResult::blocked($listingId, $this->status);
    }

    public function findByListingId(string $listingId): ?PublicListingProjectionSources
    {
        return null;
    }
}

final class CountingSource implements InspectablePublicListingProjectionSource
{
    public int $calls = 0;

    public function inspect(string $listingId): ProjectionSourceAssemblyResult
    {
        $this->calls++;

        return ProjectionSourceAssemblyResult::blocked($listingId, ProjectionSourceAssemblyStatus::ListingMissing);
    }

    public function findByListingId(string $listingId): ?PublicListingProjectionSources
    {
        return null;
    }
}

final class LookupMediaRegistry implements MediaCollectionRegistry
{
    public function __construct(private ?MediaCollection $collection) {}

    public function find(MediaCollectionId $id): ?MediaCollection
    {
        return $this->collection;
    }

    public function add(MediaCollection $collection): void {}

    public function save(MediaCollection $collection, int $expectedVersion): void {}

    public function saveWithMediaReservation(MediaCollection $collection, MediaId $mediaId, int $expectedVersion): void {}
}

final class LookupTargetResolver implements PropertyListingsResolver
{
    /** @param list<PropertyListingsPage> $pages */
    public function __construct(private array $pages) {}

    public function readPage(string $propertyId, ?string $checkpoint, int $limit): PropertyListingsPage
    {
        return array_shift($this->pages) ?? throw new \LogicException('Missing lookup page.');
    }
}

final readonly class FixedMediaPropertyResolver implements MediaCollectionPropertyResolver
{
    public function __construct(private MediaCollectionPropertyStatus $status) {}

    public function resolve(string $mediaCollectionId): MediaCollectionPropertyResolution
    {
        return new MediaCollectionPropertyResolution($this->status);
    }
}

final class LookupRecordingUpdater implements PublicProjectionUpdateExecutor
{
    /** @var list<string> */
    public array $calls = [];

    public function update(string $listingId): PublicListingProjectionUpdateResult
    {
        $this->calls[] = $listingId;

        return new PublicListingProjectionUpdateResult(PublicListingProjectionUpdateOutcome::Applied);
    }
}
