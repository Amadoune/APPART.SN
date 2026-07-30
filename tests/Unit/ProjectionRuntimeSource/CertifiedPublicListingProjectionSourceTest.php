<?php

namespace Tests\Unit\ProjectionRuntimeSource;

use App\Application\ActiveGenerationReader\ActiveGenerationReadResult;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\DecisionTimeSource\Contract\DecisionTimeReader;
use App\Application\DecisionTimeSource\DecisionTimeReadResult;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionReader;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyReadResult;
use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicMediaSource\PublicMediaReadResult;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdater;
use App\Infrastructure\ProjectionRuntimeSource\CertifiedPublicListingProjectionSource;
use App\Projections\SearchListingProjectionBuilder;
use App\Projections\SeoListingProjectionBuilder;
use App\ReadModels\PublicListingReadModelBuilder;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\ListingSeoDecisionPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as SeoListingId;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
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
use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResult;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
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
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId as SearchListingId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\ContentSeoSourceSnapshotFixture;
use Tests\Support\SearchDecisionFixture;

final class CertifiedPublicListingProjectionSourceTest extends TestCase
{
    private const string LISTING = '99400000-0000-4000-8000-000000000001';

    private const string PROPERTY = '92000000-0000-4000-8000-000000000001';

    private const string COLLECTION = '93000000-0000-4000-8000-000000000001';

    private const string GENERATION = '99000000-0000-4000-8000-000000000001';

    public function test_all_certified_sources_assemble_a_deterministic_promotable_source_consumed_by_updater(): void
    {
        $scenario = self::scenario();
        $source = $scenario->source();
        $first = $source->inspect(self::LISTING);
        $second = $source->inspect(self::LISTING);

        self::assertSame(ProjectionSourceAssemblyStatus::Found, $first->status);
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $first->readiness);
        self::assertEquals($first->sources, $second->sources);
        self::assertEquals($first->watermark, $second->watermark);

        $writer = new CapturingProjectionWriter;
        $updater = new PublicListingProjectionUpdater($source, new SearchListingProjectionBuilder, new ListingSeoDecisionPolicy(new CanonicalPolicy, new CanonicalHistoryPolicy), new SeoListingProjectionBuilder, new PublicListingReadModelBuilder, $writer);
        self::assertSame(PublicListingProjectionUpdateOutcome::Applied, $updater->update(self::LISTING)->outcome);
        self::assertNotNull($writer->record);
        self::assertSame(self::GENERATION, $writer->record->generationId->value);
    }

    public function test_missing_geography_or_media_produces_an_explicit_incomplete_watermark(): void
    {
        $scenario = self::scenario();
        $scenario->geography = PublicGeographyReadResult::missing('place:dakar:plateau');
        $geography = $scenario->source()->inspect(self::LISTING);
        self::assertSame(ProjectionSourceAssemblyStatus::Found, $geography->status);
        self::assertSame(PublicProjectionPromotionReadiness::MissingPublicGeographyVersion, $geography->readiness);

        $scenario = self::scenario();
        $scenario->publicMedia = PublicMediaReadResult::missing(self::COLLECTION);
        $media = $scenario->source()->inspect(self::LISTING);
        self::assertSame(ProjectionSourceAssemblyStatus::Found, $media->status);
        self::assertSame(PublicProjectionPromotionReadiness::MissingPublicMediaVersion, $media->readiness);
    }

    public function test_missing_or_corrupted_search_and_content_are_explained(): void
    {
        $scenario = self::scenario();
        $scenario->search = SearchDecisionReadResult::missing(SearchListingId::fromString(self::LISTING));
        self::assertSame(ProjectionSourceAssemblyStatus::SearchMissing, $scenario->source()->inspect(self::LISTING)->status);
        $scenario->search = SearchDecisionReadResult::corrupted(SearchListingId::fromString(self::LISTING));
        self::assertSame(ProjectionSourceAssemblyStatus::SearchCorrupted, $scenario->source()->inspect(self::LISTING)->status);

        $scenario = self::scenario();
        $scenario->contentSeo = ContentSeoSnapshotReadResult::missing(SeoListingId::fromString(self::LISTING));
        self::assertSame(ProjectionSourceAssemblyStatus::ContentSeoMissing, $scenario->source()->inspect(self::LISTING)->status);
        $scenario->contentSeo = ContentSeoSnapshotReadResult::corrupted(SeoListingId::fromString(self::LISTING));
        self::assertSame(ProjectionSourceAssemblyStatus::ContentSeoCorrupted, $scenario->source()->inspect(self::LISTING)->status);
    }

    public function test_generation_and_decision_time_failures_are_explained_without_fallback(): void
    {
        $scenario = self::scenario();
        $scenario->generation = ActiveGenerationReadResult::missing();
        self::assertSame(ProjectionSourceAssemblyStatus::ActiveGenerationMissing, $scenario->source()->inspect(self::LISTING)->status);
        $scenario->generation = ActiveGenerationReadResult::corrupted();
        self::assertSame(ProjectionSourceAssemblyStatus::ActiveGenerationCorrupted, $scenario->source()->inspect(self::LISTING)->status);

        $scenario = self::scenario();
        $scenario->decisionTime = DecisionTimeReadResult::missing(self::LISTING);
        self::assertSame(ProjectionSourceAssemblyStatus::DecisionTimeMissing, $scenario->source()->inspect(self::LISTING)->status);
        $scenario->decisionTime = DecisionTimeReadResult::found(self::LISTING, new DateTimeImmutable('2025-01-01T00:00:00+00:00'));
        self::assertSame(ProjectionSourceAssemblyStatus::DecisionTimeDivergent, $scenario->source()->inspect(self::LISTING)->status);
    }

    public function test_corrupt_public_decisions_and_media_ownership_are_explained(): void
    {
        $scenario = self::scenario();
        $scenario->geography = PublicGeographyReadResult::corrupted('place:dakar:plateau');
        self::assertSame(ProjectionSourceAssemblyStatus::PublicGeographyCorrupted, $scenario->source()->inspect(self::LISTING)->status);
        $scenario = self::scenario();
        $scenario->publicMedia = PublicMediaReadResult::corrupted(self::COLLECTION);
        self::assertSame(ProjectionSourceAssemblyStatus::PublicMediaCorrupted, $scenario->source()->inspect(self::LISTING)->status);
        $scenario = self::scenario();
        $scenario->ownership = MediaOwnershipResult::ambiguous(MediaPropertyId::fromString(self::PROPERTY));
        self::assertSame(ProjectionSourceAssemblyStatus::MediaOwnershipAmbiguous, $scenario->source()->inspect(self::LISTING)->status);
    }

    public static function scenario(): ProjectionSourceScenario
    {
        $at = new DateTimeImmutable('2026-07-19T10:00:00+00:00');
        $property = Property::register(PropertyId::fromString(self::PROPERTY), PropertyReference::fromString('SOURCE-PROPERTY-001'), PropertyType::Apartment, SurfaceArea::fromSquareMeters(120), RoomCount::fromInt(5), BathroomCount::fromInt(2), ConstructionYear::fromInt(2020), new Address(AddressId::fromString('70000000-0000-4000-8000-000000000010'), GeographicPlaceId::fromString('place:dakar:plateau'), AddressLine::fromString('Adresse source')), BusinessYear::fromInt(2026), new PropertyTypePolicy, $at);
        $listing = Listing::createDraft(ListingId::fromString(self::LISTING), ListingPropertyId::fromString(self::PROPERTY), ListingRevisionId::fromString('71000000-0000-4000-8000-000000000001'), new TransitionEvidence(ActorId::fromString('actor:source'), TransitionTrigger::DraftStarted, TransitionReason::fromString('Projection source fixture'), TransitionOrigin::Advertiser, $at), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $listing->submit(ListingRevisionId::fromString('71000000-0000-4000-8000-000000000002'), new TransitionEvidence(ActorId::fromString('actor:source'), TransitionTrigger::SubmissionConfirmed, TransitionReason::fromString('Projection source fixture'), TransitionOrigin::Advertiser, $at->modify('+1 minute')), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $listing->sendToReview(ListingRevisionId::fromString('71000000-0000-4000-8000-000000000003'), new TransitionEvidence(ActorId::fromString('actor:source'), TransitionTrigger::ReviewStarted, TransitionReason::fromString('Projection source fixture'), TransitionOrigin::Moderation, $at->modify('+2 minutes')), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $listing->publish(ListingRevisionId::fromString('71000000-0000-4000-8000-000000000004'), ExpirationDate::fromDateTime($at->modify('+120 days')), new TransitionEvidence(ActorId::fromString('actor:source'), TransitionTrigger::FavorableReview, TransitionReason::fromString('Projection source fixture'), TransitionOrigin::Moderation, $at->modify('+3 minutes')), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $media = MediaCollection::create(MediaCollectionId::fromString(self::COLLECTION), MediaPropertyId::fromString(self::PROPERTY), $at);
        $media->add(MediaId::fromString('93000000-0000-4000-8000-000000000101'), MediaType::Image, MediaChecksum::fromSha256(str_repeat('a', 64)), MediaOrder::fromInt(1), null, MediaSource::Owner, $at);
        $baseSnapshot = ContentSeoSourceSnapshotFixture::make();
        $snapshot = new ContentSeoSourceDecision(
            $baseSnapshot->snapshotId,
            $baseSnapshot->listingId,
            $baseSnapshot->version,
            new ListingSeoSource(
                $baseSnapshot->listingId,
                $baseSnapshot->listing->state,
                'Appartement moderne et lumineux à Dakar Plateau',
                'Découvrez cet appartement moderne, lumineux et idéalement situé au cœur de Dakar pour votre prochain logement.',
                $baseSnapshot->listing->canonicalPath,
                $baseSnapshot->listing->revision,
                $at->modify('+3 minutes'),
                $at->modify('+120 days'),
                $baseSnapshot->listing->expiredTreatment,
                $baseSnapshot->listing->nonIndexablePageTreatment,
            ),
            $baseSnapshot->search,
            $baseSnapshot->property,
            $baseSnapshot->canonicalHistory,
            $baseSnapshot->decisionAt,
        );
        $geographyItems = [new PublicGeographyBreadcrumbItem('Accueil', 'https://appart.sn/accueil'), new PublicGeographyBreadcrumbItem('Dakar', 'https://appart.sn/dakar')];
        $geographyPayload = json_encode(['locality' => 'Dakar', 'breadcrumb' => array_map(static fn ($item) => ['label' => $item->label, 'url' => $item->url], $geographyItems)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $geography = new PublicGeographyDecision('place:dakar:plateau', (new PublicGeographyRevisionStrategy)->revise(3, $geographyPayload, 'place:3:published'), 'Dakar', $geographyItems);
        $cover = new PublicMediaItem('media:cover', 'https://media.appart.sn/cover.webp', []);
        $mediaPayload = json_encode(['cover' => $cover->canonicalData(), 'gallery' => [$cover->canonicalData()]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $publicMedia = new PublicMediaDecision(self::COLLECTION, (new PublicMediaRevisionStrategy)->revise(4, $mediaPayload, 'media:4:published'), $cover, [$cover]);

        return new ProjectionSourceScenario($listing, $property, $media, SearchDecisionReadResult::found(SearchListingId::fromString(self::LISTING), SearchDecisionFixture::make(5, 500, self::LISTING)), ContentSeoSnapshotReadResult::found($snapshot->listingId, $snapshot), PublicGeographyReadResult::found('place:dakar:plateau', $geography), PublicMediaReadResult::found(self::COLLECTION, $publicMedia), ActiveGenerationReadResult::found(new PublicProjectionGeneration(PublicProjectionGenerationId::fromString(self::GENERATION), PublicProjectionGenerationState::Active)), DecisionTimeReadResult::found(self::LISTING, $snapshot->decisionAt), MediaOwnershipResult::found(MediaPropertyId::fromString(self::PROPERTY), MediaCollectionId::fromString(self::COLLECTION)));
    }
}

final class ProjectionSourceScenario
{
    public function __construct(
        public Listing $listing,
        public Property $property,
        public MediaCollection $media,
        public SearchDecisionReadResult $search,
        public ContentSeoSnapshotReadResult $contentSeo,
        public PublicGeographyReadResult $geography,
        public PublicMediaReadResult $publicMedia,
        public ActiveGenerationReadResult $generation,
        public DecisionTimeReadResult $decisionTime,
        public MediaOwnershipResult $ownership,
    ) {}

    public function source(): CertifiedPublicListingProjectionSource
    {
        return new CertifiedPublicListingProjectionSource(
            new FakeListingRegistry($this->listing), new FakePropertyRegistry($this->property), new FakeMediaOwnershipLookup($this->ownership), new FakeMediaRegistry($this->media),
            new FakeSearchDecisionReader($this->search), new FakeContentSeoSnapshotReader($this->contentSeo), new FakeGeographyReader($this->geography), new FakePublicMediaReader($this->publicMedia),
            new FakeActiveGenerationReader($this->generation), new FakeDecisionTimeReader($this->decisionTime),
        );
    }
}

final readonly class FakeListingRegistry implements ListingRegistry
{
    public function __construct(private Listing $value) {}

    public function find(ListingId $id): ?Listing
    {
        return $this->value;
    }

    public function add(Listing $listing): void {}

    public function save(Listing $listing, int $expectedVersion): void {}
}
final readonly class FakePropertyRegistry implements PropertyRegistry
{
    public function __construct(private Property $value) {}

    public function find(PropertyId $id): ?Property
    {
        return $this->value;
    }

    public function add(Property $property): void {}

    public function save(Property $property, int $expectedVersion): void {}
}
final readonly class FakeMediaRegistry implements MediaCollectionRegistry
{
    public function __construct(private MediaCollection $value) {}

    public function find(MediaCollectionId $id): ?MediaCollection
    {
        return $this->value;
    }

    public function add(MediaCollection $collection): void {}

    public function save(MediaCollection $collection, int $expectedVersion): void {}

    public function saveWithMediaReservation(MediaCollection $collection, MediaId $mediaId, int $expectedVersion): void {}
}
final readonly class FakeMediaOwnershipLookup implements MediaCollectionOwnershipLookup
{
    public function __construct(private MediaOwnershipResult $value) {}

    public function resolve(MediaPropertyId $propertyId): MediaOwnershipResult
    {
        return $this->value;
    }
}
final readonly class FakeSearchDecisionReader implements SearchDecisionReader
{
    public function __construct(private SearchDecisionReadResult $value) {}

    public function readByListing(SearchListingId $listingId): SearchDecisionReadResult
    {
        return $this->value;
    }
}
final readonly class FakeContentSeoSnapshotReader implements ContentSeoSourceSnapshotReader
{
    public function __construct(private ContentSeoSnapshotReadResult $value) {}

    public function readByListing(SeoListingId $listingId): ContentSeoSnapshotReadResult
    {
        return $this->value;
    }
}
final readonly class FakeGeographyReader implements PublicGeographyDecisionReader
{
    public function __construct(private PublicGeographyReadResult $value) {}

    public function read(string $placeId): PublicGeographyReadResult
    {
        return $this->value;
    }
}
final readonly class FakePublicMediaReader implements PublicMediaDecisionReader
{
    public function __construct(private PublicMediaReadResult $value) {}

    public function read(string $mediaCollectionId): PublicMediaReadResult
    {
        return $this->value;
    }
}
final readonly class FakeActiveGenerationReader implements ActiveGenerationReader
{
    public function __construct(private ActiveGenerationReadResult $value) {}

    public function read(): ActiveGenerationReadResult
    {
        return $this->value;
    }
}
final readonly class FakeDecisionTimeReader implements DecisionTimeReader
{
    public function __construct(private DecisionTimeReadResult $value) {}

    public function readByListing(string $listingId): DecisionTimeReadResult
    {
        return $this->value;
    }
}

final class CapturingProjectionWriter implements PublicListingProjectionWriter
{
    public ?PublicListingProjectionRecord $record = null;

    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        $this->record = $record;

        return PublicProjectionWriteResult::Applied;
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::Applied;
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::Applied;
    }

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::Applied;
    }
}
