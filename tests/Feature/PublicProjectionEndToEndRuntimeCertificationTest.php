<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryOutcome;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyWriter;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaWriter;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as SeoListingId;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
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
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
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
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use DateTimeImmutable;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\ContentSeoSourceSnapshotFixture;
use Tests\Support\SearchDecisionFixture;
use Tests\TestCase;

final class PublicProjectionEndToEndRuntimeCertificationTest extends TestCase
{
    private const string LISTING = '99400000-0000-4000-8000-000000000001';

    private const string PROPERTY = '92000000-0000-4000-8000-000000000001';

    private const string COLLECTION = '93000000-0000-4000-8000-000000000001';

    private const string GENERATION = '99000000-0000-4000-8000-000000000001';

    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->app->instance(PDO::class, $this->connection);
    }

    public function test_listing_mutation_reaches_the_public_http_projection_through_the_production_runtime(): void
    {
        $this->prepareCertifiedSources();
        $consumerId = $this->app->make(PublicProjectionOutboxConsumerId::class);
        $message = $this->message(1);

        $this->app->make(PostgreSqlAggregateOutboxTransaction::class)->run(function () use ($message, $consumerId): void {
            $this->app->make(ListingRegistry::class)->add($this->publishedListing());
            self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->app->make(PublicProjectionOutboxWriter::class)->append($message, $consumerId));
        });

        self::assertSame(PublicProjectionDeliveryStatus::Pending, $this->deliveryRecords($consumerId)[0]->status);
        $worker = $this->app->make(PublicProjectionDeliveryWorker::class);
        $batch = $worker->runOnce($consumerId);
        self::assertSame(1, $batch->count(PublicProjectionDeliveryOutcome::Delivered), json_encode(array_map(static fn ($outcome): array => [$outcome->outcome->value, $outcome->technicalCode], $batch->outcomes), JSON_THROW_ON_ERROR));
        self::assertSame(PublicProjectionDeliveryStatus::Delivered, $this->deliveryRecords($consumerId)[0]->status);

        $projection = $this->app->make(PublicListingQuery::class)->findByCanonicalPath('annonces/appartement-dakar');
        self::assertNotNull($projection);
        $this->get('/annonces/appartement-dakar')
            ->assertOk()
            ->assertViewHas('listing', fn ($actual): bool => $actual == $projection);
        self::assertSame(RuntimeHealthStatus::Healthy, $this->app->make(RuntimeHealthInspector::class)->inspect()->status);

        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->app->make(PublicProjectionOutboxWriter::class)->append($this->message(2), $consumerId));
        self::assertSame(1, $worker->runOnce($consumerId)->count(PublicProjectionDeliveryOutcome::AlreadyConsumed));
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM public_projection.listing_projections')->fetchColumn());
    }

    public function test_property_and_media_events_traverse_all_runtime_pages_without_double_effect(): void
    {
        $this->prepareCertifiedSources(false);
        $this->app->make(PostgreSqlAggregateOutboxTransaction::class)->run(function (): void {
            for ($index = 1; $index <= 101; $index++) {
                $this->app->make(ListingRegistry::class)->add($this->publishedListing($this->listingId($index), $index));
            }
        });
        for ($index = 1; $index <= 101; $index++) {
            $listingId = $this->listingId($index);
            $this->app->make(PostgreSqlSearchDecisionWriter::class)->store(SearchDecisionFixture::make($index, 500, $listingId));
            $this->app->make(PostgreSqlContentSeoSourceSnapshotWriter::class)->store($this->contentSeo($listingId, $index, $this->canonical($index)));
        }

        $consumerId = $this->app->make(PublicProjectionOutboxConsumerId::class);
        $worker = $this->app->make(PublicProjectionDeliveryWorker::class);
        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->app->make(PublicProjectionOutboxWriter::class)->append($this->propertyMessage(), $consumerId));
        self::assertSame(1, $worker->runOnce($consumerId)->count(PublicProjectionDeliveryOutcome::Delivered));
        self::assertSame(101, (int) $this->connection->query('SELECT count(*) FROM public_projection.listing_projections')->fetchColumn());
        self::assertNotNull($this->app->make(PublicListingQuery::class)->findByCanonicalPath($this->canonical(101)));

        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->app->make(PublicProjectionOutboxWriter::class)->append($this->mediaMessage(), $consumerId));
        self::assertSame(1, $worker->runOnce($consumerId)->count(PublicProjectionDeliveryOutcome::AlreadyConsumed));
        self::assertSame(101, (int) $this->connection->query('SELECT count(*) FROM public_projection.listing_projections')->fetchColumn());
    }

    private function prepareCertifiedSources(bool $listingSources = true): void
    {
        $this->app->make(PostgreSqlAggregateOutboxTransaction::class)->run(function (): void {
            $this->app->make(PropertyRegistry::class)->add($this->property());
            $media = $this->media();
            $registry = $this->app->make(MediaCollectionRegistry::class);
            $registry->add($media);
            $mediaId = MediaId::fromString('93000000-0000-4000-8000-000000000101');
            $media->add($mediaId, MediaType::Image, MediaChecksum::fromSha256(str_repeat('a', 64)), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->at());
            $registry->saveWithMediaReservation($media, $mediaId, 0);
        });
        if ($listingSources) {
            $this->app->make(PostgreSqlSearchDecisionWriter::class)->store(SearchDecisionFixture::make(5, 500, self::LISTING));
            $this->app->make(PostgreSqlContentSeoSourceSnapshotWriter::class)->store($this->contentSeo());
        }
        $this->app->make(PostgreSqlPublicGeographyWriter::class)->store($this->geography());
        $this->app->make(PostgreSqlPublicMediaWriter::class)->store($this->publicMedia());
        $statement = $this->connection->prepare("INSERT INTO public_projection.generations(generation_id,state) VALUES (:generation,'active')");
        $statement->execute(['generation' => self::GENERATION]);
    }

    private function property(): Property
    {
        return Property::register(PropertyId::fromString(self::PROPERTY), PropertyReference::fromString('RUNTIME-E2E-001'), PropertyType::Apartment, SurfaceArea::fromSquareMeters(120), RoomCount::fromInt(5), BathroomCount::fromInt(2), ConstructionYear::fromInt(2020), new Address(AddressId::fromString('70000000-0000-4000-8000-000000000010'), GeographicPlaceId::fromString('place:dakar'), AddressLine::fromString('Adresse runtime')), BusinessYear::fromInt(2026), new PropertyTypePolicy, $this->at());
    }

    private function media(): MediaCollection
    {
        return MediaCollection::create(MediaCollectionId::fromString(self::COLLECTION), MediaPropertyId::fromString(self::PROPERTY), $this->at());
    }

    private function publishedListing(string $listingId = self::LISTING, int $index = 1): Listing
    {
        $listing = Listing::createDraft(ListingId::fromString($listingId), ListingPropertyId::fromString(self::PROPERTY), $this->revision($index, 1), $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $listing->submit($this->revision($index, 2), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $listing->sendToReview($this->revision($index, 3), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2), new ListingTransitionPolicy, PropertyAvailability::Eligible);
        $listing->publish($this->revision($index, 4), ExpirationDate::fromDateTime($this->at()->modify('+120 days')), $this->evidence(TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, 3), new ListingTransitionPolicy, PropertyAvailability::Eligible);

        return $listing;
    }

    private function geography(): PublicGeographyDecision
    {
        $items = [new PublicGeographyBreadcrumbItem('Accueil', 'https://appart.sn/accueil'), new PublicGeographyBreadcrumbItem('Dakar', 'https://appart.sn/dakar')];
        $payload = json_encode(['locality' => 'Dakar', 'breadcrumb' => array_map(static fn ($item) => ['label' => $item->label, 'url' => $item->url], $items)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new PublicGeographyDecision('place:dakar', (new PublicGeographyRevisionStrategy)->revise(3, $payload, 'place:3:published'), 'Dakar', $items);
    }

    private function publicMedia(): PublicMediaDecision
    {
        $cover = new PublicMediaItem('media:cover', 'https://media.appart.sn/cover.webp', []);
        $payload = json_encode(['cover' => $cover->canonicalData(), 'gallery' => [$cover->canonicalData()]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new PublicMediaDecision(self::COLLECTION, (new PublicMediaRevisionStrategy)->revise(4, $payload, 'media:4:published'), $cover, [$cover]);
    }

    private function contentSeo(string $listingId = self::LISTING, int $index = 1, string $canonical = 'annonces/appartement-dakar'): ContentSeoSourceDecision
    {
        $base = ContentSeoSourceSnapshotFixture::make();
        $identity = SeoListingId::fromString($listingId);

        return new ContentSeoSourceDecision(
            sprintf('99600000-0000-4000-8000-%012d', $index),
            $identity,
            $base->version,
            new ListingSeoSource(
                $identity,
                $base->listing->state,
                'Appartement moderne et lumineux à Dakar '.$index,
                'Découvrez cet appartement moderne, lumineux et idéalement situé au cœur de Dakar pour votre prochain logement.',
                $canonical,
                $base->listing->revision,
                $this->at()->modify('+3 minutes'),
                $this->at()->modify('+120 days'),
                $base->listing->expiredTreatment,
                $base->listing->nonIndexablePageTreatment,
            ),
            new SearchSeoSource($identity, $base->search->state, $base->search->revision),
            new PropertySeoSource($identity, $base->property->state, $base->property->propertyType, $base->property->city, $base->property->revision),
            [new CanonicalHistoryEntry(CanonicalUrl::fromString('https://appart.sn/'.$canonical), CanonicalDisposition::Current, $this->at())],
            $base->decisionAt,
        );
    }

    /** @return list<PublicProjectionOutboxRecord> */
    private function deliveryRecords(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->app->make(PublicProjectionOutboxReader::class)->findByAggregate($consumerId, PublicProjectionDeliveryAggregateId::fromString(self::LISTING));
    }

    private function message(int $version): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryAggregateId::fromString(self::LISTING), new PublicProjectionDeliveryOrder($version, PublicProjectionDeliveryEventIndex::fromInt(1)), $this->at(), new PublicProjectionDeliveryListingPayload(self::LISTING));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $this->at()->modify('+1 minute'));
    }

    private function revision(int $index, int $suffix): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('71000000-0000-4000-8000-%012d', ($index * 10) + $suffix));
    }

    private function propertyMessage(): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('property.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'), PublicProjectionDeliveryAggregateType::fromString('Property'), PublicProjectionDeliveryAggregateId::fromString(self::PROPERTY), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), $this->at(), new PublicProjectionDeliveryPropertyPayload(self::PROPERTY));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $this->at()->modify('+1 minute'));
    }

    private function mediaMessage(): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('media.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('Media'), PublicProjectionDeliveryAggregateType::fromString('MediaCollection'), PublicProjectionDeliveryAggregateId::fromString(self::COLLECTION), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), $this->at(), new PublicProjectionDeliveryMediaPayload(self::COLLECTION));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $this->at()->modify('+1 minute'));
    }

    private function listingId(int $index): string
    {
        return sprintf('99400000-0000-4000-8000-%012d', $index);
    }

    private function canonical(int $index): string
    {
        return 'annonces/runtime-'.$index;
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin, int $minutes): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor:runtime-e2e'), $trigger, TransitionReason::fromString('Runtime certification'), $origin, $this->at()->modify("+{$minutes} minutes"));
    }

    private function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-19T10:00:00+00:00');
    }
}
