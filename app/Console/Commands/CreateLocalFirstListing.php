<?php

namespace App\Console\Commands;

use App\Application\Contract\PublicListingQuery;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationAtomicTransaction;
use App\Application\ProjectionRuntimeSource\Contract\CandidatePublicListingProjectionSource;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationManager;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifestEntry;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuilder;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyWriter;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaWriter;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as SeoListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\Contract\ListingPublicationExpirationPolicyV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract\ListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionIntentId;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionOperation;
use Appart\Modules\ListingLifecycle\Application\UseCase\PublishListing;
use Appart\Modules\ListingLifecycle\Application\UseCase\SendToReview;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId as ListingMediaCollectionId;
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
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchFacet;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId as SearchListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetKey;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use DateTimeImmutable;
use Illuminate\Console\Command;
use RuntimeException;

final class CreateLocalFirstListing extends Command
{
    protected $signature = 'appart:local:first-listing';

    protected $description = 'Create the single local P02 listing through certified application boundaries.';

    private const string PROPERTY = 'b0200000-0000-4000-8000-000000000001';

    private const string LISTING = 'b0200000-0000-4000-8000-000000000002';

    private const string COLLECTION = 'b0200000-0000-4000-8000-000000000003';

    private const string MEDIA = 'b0200000-0000-4000-8000-000000000004';

    private const string GENERATION = 'b0200000-0000-4000-8000-000000000005';

    private const string CANONICAL = 'annonces/p02-premiere-annonce-dakar';

    public function handle(): int
    {
        if (! app()->environment('local') || (string) config('database.connections.pgsql.database') !== 'appart_rebuild') {
            $this->error('P02 local data is restricted to APP_ENV=local and DB_DATABASE=appart_rebuild.');

            return self::FAILURE;
        }

        $at = new DateTimeImmutable('2026-08-09T12:00:00+00:00');
        $propertyId = PropertyId::fromString(self::PROPERTY);
        $listingId = ListingId::fromString(self::LISTING);
        $collectionId = MediaCollectionId::fromString(self::COLLECTION);
        $properties = app(PropertyRegistry::class);
        $listings = app(ListingRegistry::class);
        $media = app(MediaCollectionRegistry::class);

        app(ListingPublicationAtomicTransaction::class)->run(function () use ($at, $propertyId, $listingId, $collectionId, $properties, $listings, $media): void {
            $property = $properties->find($propertyId);
            if ($property === null) {
                $properties->add(Property::register(
                    $propertyId,
                    PropertyReference::fromString('DEV-P02-FIRST-LISTING'),
                    PropertyType::Apartment,
                    SurfaceArea::fromSquareMeters(86),
                    RoomCount::fromInt(3),
                    BathroomCount::fromInt(2),
                    ConstructionYear::fromInt(2024),
                    new Address(AddressId::fromString('b0200000-0000-4000-8000-000000000006'), GeographicPlaceId::fromString('place:p02:dakar'), AddressLine::fromString('Dakar — donnée locale P02')),
                    BusinessYear::fromInt(2026),
                    new PropertyTypePolicy,
                    $at,
                ));
            } elseif ($property->address()?->placeId->value !== 'place:p02:dakar') {
                $version = $property->version();
                $property->changeAddress(new Address(AddressId::fromString('b0200000-0000-4000-8000-000000000006'), GeographicPlaceId::fromString('place:p02:dakar'), AddressLine::fromString('Dakar — donnée locale P02')), $at);
                $properties->save($property, $version);
            }
            if ($media->find($collectionId) === null) {
                $collection = MediaCollection::create($collectionId, MediaPropertyId::fromString(self::PROPERTY), $at);
                $media->add($collection);
                $mediaId = MediaId::fromString(self::MEDIA);
                $collection->add($mediaId, MediaType::Image, MediaChecksum::fromSha256(hash('sha256', 'APPART.SN P02 local first listing')), MediaOrder::fromInt(1), null, MediaSource::Owner, $at);
                $media->saveWithMediaReservation($collection, $mediaId, 0);
            }
            if ($listings->find($listingId) === null) {
                $draft = Listing::createDraft(
                    $listingId,
                    ListingPropertyId::fromString(self::PROPERTY),
                    ListingRevisionId::fromString('b0200000-0000-4000-8000-000000000010'),
                    new TransitionEvidence(ActorId::fromString('actor:p02-owner'), TransitionTrigger::DraftStarted, TransitionReason::fromString('Création locale explicite de la première annonce P02.'), TransitionOrigin::Advertiser, $at),
                    new ListingTransitionPolicy,
                    PropertyAvailability::Eligible,
                );
                $listings->add($draft);
                $initialized = app(ListingPublicationWorkflowStore::class)->initialize($listingId, ListingPublicationState::Draft);
                if (! in_array($initialized->value, ['applied', 'already_applied'], true)) {
                    throw new RuntimeException('The Listing publication workflow could not be initialized.');
                }
            }
        });

        $this->publish($listingId, $at);
        $this->materializePublicSources($at);
        $this->activateProjection($listingId);

        $listing = $listings->find($listingId);
        $public = app(PublicListingQuery::class)->findByCanonicalPath(self::CANONICAL);
        if ($listing?->status()->value !== 'published' || $public === null) {
            throw new RuntimeException('P02 did not converge to a public Listing.');
        }

        $this->info('APPART.SN P02 first listing is available.');
        $this->line('listingStatus='.$listing->status()->value);
        $this->line('workflowStatus='.app(ListingPublicationWorkflowStore::class)->read($listingId)->snapshot?->state->value);
        $this->line('canonicalPath='.self::CANONICAL);
        $this->line('expiration='.$listing->expirationDate()?->value->format('Y-m-d\TH:i:s.uP'));

        return self::SUCCESS;
    }

    private function publish(ListingId $listingId, DateTimeImmutable $at): void
    {
        $allocator = app(ListingRevisionAllocatorV1::class);
        $this->publishStep($listingId, $allocator, ListingPublicationAction::Submit, ListingRevisionOperation::Submit, 'b0200000-0000-4000-8000-000000000021', 1, TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, $at->modify('+1 minute'));
        $this->publishStep($listingId, $allocator, ListingPublicationAction::BeginReview, ListingRevisionOperation::BeginReview, 'b0200000-0000-4000-8000-000000000022', 2, TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, $at->modify('+2 minutes'));
        $this->publishStep($listingId, $allocator, ListingPublicationAction::ApproveAndPublish, ListingRevisionOperation::ApproveAndPublish, 'b0200000-0000-4000-8000-000000000023', 3, TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, $at->modify('+3 minutes'));
    }

    private function publishStep(
        ListingId $listingId,
        ListingRevisionAllocatorV1 $allocator,
        ListingPublicationAction $action,
        ListingRevisionOperation $operation,
        string $intent,
        int $expected,
        TransitionTrigger $trigger,
        TransitionOrigin $origin,
        DateTimeImmutable $occurredAt,
    ): void {
        $current = app(ListingRegistry::class)->find($listingId);
        if ($current?->version() >= $expected) {
            return;
        }

        $revision = $allocator->allocate($listingId, $operation, ListingRevisionIntentId::fromString($intent));
        $actor = $origin === TransitionOrigin::Advertiser ? 'actor:p02-owner' : 'actor:p02-moderator';
        $evidence = new TransitionEvidence(ActorId::fromString($actor), $trigger, null, $origin, $occurredAt);
        app(ListingPublicationAtomicTransaction::class)->run(function () use ($action, $expected, $listingId, $revision, $evidence, $occurredAt): void {
            $result = app(ListingPublicationOrchestrator::class)->transition(new ListingPublicationOrchestrationRequest($listingId, $action, $expected));
            if (! in_array($result->status, [ListingPublicationOrchestrationStatus::Applied, ListingPublicationOrchestrationStatus::AlreadyApplied], true)) {
                throw new RuntimeException('The Listing publication workflow transition was rejected.');
            }

            match ($action) {
                ListingPublicationAction::Submit => app(SubmitListing::class)->execute($listingId, $revision, $evidence),
                ListingPublicationAction::BeginReview => app(SendToReview::class)->execute($listingId, $revision, $evidence),
                ListingPublicationAction::ApproveAndPublish => app(PublishListing::class)->execute(
                    $listingId,
                    ListingMediaCollectionId::fromString(self::COLLECTION),
                    $revision,
                    app(ListingPublicationExpirationPolicyV1::class)->expirationFor($occurredAt),
                    $evidence,
                ),
                default => throw new RuntimeException('Unsupported P02 transition.'),
            };
        });
    }

    private function materializePublicSources(DateTimeImmutable $at): void
    {
        $listingId = SearchListingId::fromString(self::LISTING);
        $revisions = new SourceRevisionSet(
            SourceRevision::create(SourceKind::Listing, 1, 'b0200000-0000-4000-8000-000000000031', $at),
            SourceRevision::create(SourceKind::Property, 1, 'b0200000-0000-4000-8000-000000000032', $at),
            SourceRevision::create(SourceKind::Media, 1, 'b0200000-0000-4000-8000-000000000033', $at),
        );
        app(PostgreSqlSearchDecisionWriter::class)->store(new SearchDecision(
            SearchIndexId::fromString('b0200000-0000-4000-8000-000000000034'),
            $listingId,
            1,
            SearchProjection::derived(ProjectionState::Visible, SearchRank::fromInt(500), [new SearchFacet(SearchFacetKey::fromString('property.type'), SearchFacetValue::fromString('apartment'), SourceKind::Property)], $revisions),
        ));

        $seoId = SeoListingId::fromString(self::LISTING);
        $coherence = 'b0200000-0000-4000-8000-000000000035';
        $revision = static fn (SeoSourceKind $kind, string $fact): SeoSourceRevision => SeoSourceRevision::create($kind, 1, $fact, $coherence, $at);
        $publishedAt = $at->modify('+3 minutes');
        $expiresAt = app(ListingPublicationExpirationPolicyV1::class)->expirationFor($publishedAt)->value;
        app(PostgreSqlContentSeoSourceSnapshotWriter::class)->store(new ContentSeoSourceDecision(
            'b0200000-0000-4000-8000-000000000036',
            $seoId,
            1,
            new ListingSeoSource($seoId, ListingSeoState::Published, 'Appartement lumineux à Dakar', 'Première annonce locale réelle publiée par le pipeline certifié APPART.SN.', self::CANONICAL, $revision(SeoSourceKind::Listing, 'b0200000-0000-4000-8000-000000000037'), $publishedAt, $expiresAt),
            new SearchSeoSource($seoId, SearchSeoState::Public, $revision(SeoSourceKind::Search, 'b0200000-0000-4000-8000-000000000038')),
            new PropertySeoSource($seoId, PropertySeoState::Available, 'Appartement', 'Dakar', $revision(SeoSourceKind::Property, 'b0200000-0000-4000-8000-000000000039')),
            [new CanonicalHistoryEntry(CanonicalUrl::fromString('https://appart.sn/'.self::CANONICAL), CanonicalDisposition::Current, $publishedAt)],
            $publishedAt,
        ));

        $breadcrumbs = [new PublicGeographyBreadcrumbItem('Accueil', 'https://appart.sn/accueil'), new PublicGeographyBreadcrumbItem('Dakar', 'https://appart.sn/dakar')];
        $geoPayload = json_encode(['locality' => 'Dakar', 'breadcrumb' => array_map(static fn (PublicGeographyBreadcrumbItem $item): array => ['label' => $item->label, 'url' => $item->url], $breadcrumbs)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $geography = new PublicGeographyDecision('place:p02:dakar', (new PublicGeographyRevisionStrategy)->revise(3, $geoPayload, 'p02:geography:published:v3'), 'Dakar', $breadcrumbs);
        app(PostgreSqlPublicGeographyWriter::class)->store($geography);

        $cover = new PublicMediaItem(self::MEDIA, 'https://appart.test/p02-first-listing.svg', []);
        $mediaPayload = json_encode(['cover' => $cover->canonicalData(), 'gallery' => [$cover->canonicalData()]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $publicMedia = new PublicMediaDecision(self::COLLECTION, (new PublicMediaRevisionStrategy)->revise(2, $mediaPayload, 'p02:media:published:v2'), $cover, [$cover]);
        app(PostgreSqlPublicMediaWriter::class)->store($publicMedia);
    }

    private function activateProjection(ListingId $listingId): void
    {
        if (app(PublicListingQuery::class)->findByCanonicalPath(self::CANONICAL) !== null) {
            return;
        }
        $generation = PublicProjectionGenerationId::fromString(self::GENERATION);
        $manager = app(PublicProjectionGenerationManager::class);
        $created = $manager->createCandidate($generation);
        if (! in_array($created, [PublicProjectionGenerationTransition::Applied, PublicProjectionGenerationTransition::AlreadyApplied], true)) {
            throw new RuntimeException('The P02 projection candidate could not be created.');
        }
        $source = app(CandidatePublicListingProjectionSource::class)->inspectForGeneration($listingId->value, $generation);
        if ($source->sources === null) {
            throw new RuntimeException('The P02 projection sources are blocked: '.$source->status->value.'.');
        }
        $report = app(PublicProjectionRebuilder::class)->runOnce($generation, PublicProjectionRebuildScope::listings([$listingId->value]));
        if ($report->applied + $report->alreadyApplied !== 1 || $report->missing !== 0 || $report->rejectedListingIds !== []) {
            throw new RuntimeException(sprintf(
                'The P02 projection candidate could not be rebuilt (processed=%d applied=%d already=%d missing=%d rejected=%s).',
                $report->processed,
                $report->applied,
                $report->alreadyApplied,
                $report->missing,
                implode(',', $report->rejectedListingIds),
            ));
        }
        $record = app(PostgreSqlPublicListingProjectionReader::class)->forListing($generation, $listingId->value);
        if ($record === null) {
            throw new RuntimeException('The P02 projection candidate is missing.');
        }
        $activated = $manager->activate($generation, new PublicProjectionGenerationManifest([new PublicProjectionGenerationManifestEntry($listingId->value, $record->watermark)]));
        if (! in_array($activated, [PublicProjectionGenerationTransition::Applied, PublicProjectionGenerationTransition::AlreadyApplied], true)) {
            throw new RuntimeException('The P02 projection generation could not be activated.');
        }
    }
}
