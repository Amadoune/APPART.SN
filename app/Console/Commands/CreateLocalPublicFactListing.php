<?php

namespace App\Console\Commands;

use App\Application\Contract\PublicListingQuery;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationAtomicTransaction;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperation;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationStatus;
use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdater;
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
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
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
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

final class CreateLocalPublicFactListing extends Command
{
    protected $signature = 'appart:local:public-fact-listing';

    protected $description = 'Create a local sale listing through Authoring, Lifecycle, and Public Projection.';

    private const string PROPERTY = 'b0200000-0000-4000-8000-000000000001';

    private const string LISTING = 'b0300000-0000-4000-8000-000000000002';

    private const string COLLECTION = 'b0200000-0000-4000-8000-000000000003';

    private const string OWNER = 'b0300000-0000-4000-8000-000000000001';

    private const string CANONICAL = 'annonces/p03-appartement-a-vendre-dakar';

    public function handle(): int
    {
        if (! app()->environment('local') || (string) config('database.connections.pgsql.database') !== 'appart_rebuild') {
            $this->error('Local product data is restricted to APP_ENV=local and DB_DATABASE=appart_rebuild.');

            return self::FAILURE;
        }
        if (Artisan::call('appart:local:first-listing') !== self::SUCCESS) {
            throw new RuntimeException('The certified P02 prerequisites could not be prepared.');
        }

        $at = new DateTimeImmutable('2026-08-09T15:00:00+00:00');
        $listingId = ListingId::fromString(self::LISTING);
        if (app(ListingRegistry::class)->find($listingId)?->status()->value !== 'published') {
            $operations = app(PropertyListingAuthoringOperations::class);
            $this->expect($operations->execute($this->command(AuthoringOperation::InitiateProperty, 'b0300000-0000-4000-8000-000000000011', null, 0, [], $at)));
            $this->expect($operations->execute($this->command(AuthoringOperation::CreateListing, 'b0300000-0000-4000-8000-000000000012', self::LISTING, 0, [
                'revisionId' => 'b0300000-0000-4000-8000-000000000021',
                'title' => 'Appartement à vendre à Dakar',
                'description' => 'Annonce locale issue du handoff Authoring vers faits publics.',
                'transactionKind' => 'sale',
                'priceMinor' => 85000000,
                'currency' => 'XOF',
            ], $at->modify('+1 minute'))));
            app(ListingPublicationWorkflowStore::class)->initialize($listingId, ListingPublicationState::Draft);
            $this->expect($operations->execute($this->command(AuthoringOperation::SubmitListing, 'b0300000-0000-4000-8000-000000000013', self::LISTING, 1, [], $at->modify('+2 minutes'))));

            $allocator = app(ListingRevisionAllocatorV1::class);
            $this->registryTransition($listingId, app(SubmitListing::class), $allocator, ListingRevisionOperation::Submit, 'b0300000-0000-4000-8000-000000000031', TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, $at->modify('+2 minutes'));
            $this->lifecycleTransition($listingId, ListingPublicationAction::BeginReview, app(SendToReview::class), $allocator, ListingRevisionOperation::BeginReview, 'b0300000-0000-4000-8000-000000000032', 2, TransitionTrigger::ReviewStarted, $at->modify('+3 minutes'));
            $this->publish($listingId, $allocator, $at->modify('+4 minutes'));
        }
        $this->materializeSources($at);

        $updated = app(PublicListingProjectionUpdater::class)->update(self::LISTING);
        if (! in_array($updated->outcome, [PublicListingProjectionUpdateOutcome::Applied, PublicListingProjectionUpdateOutcome::AlreadyApplied], true)) {
            throw new RuntimeException('The public projection update failed: '.$updated->outcome->value.'.');
        }
        $public = app(PublicListingQuery::class)->findByCanonicalPath(self::CANONICAL);
        if ($public === null || $public->transactionKind !== 'sale') {
            throw new RuntimeException('The sealed sale fact is absent from the public projection.');
        }

        $this->info('APPART.SN public fact listing is available.');
        $this->line('transaction='.$public->transactionKind);
        $this->line('city='.$public->city);
        $this->line('propertyType='.$public->propertyType);
        $this->line('canonicalPath='.self::CANONICAL);

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $data */
    private function command(AuthoringOperation $operation, string $intentId, ?string $listingId, int $version, array $data, DateTimeImmutable $at): AuthoringOperationCommand
    {
        return new AuthoringOperationCommand($operation, $intentId, self::OWNER, self::PROPERTY, $listingId, $version, $data, $at);
    }

    private function expect(object $result): void
    {
        if (! isset($result->status) || ! in_array($result->status, [AuthoringOperationStatus::Applied, AuthoringOperationStatus::AlreadyApplied], true)) {
            throw new RuntimeException('The Authoring operation did not converge: '.($result->status->value ?? 'unknown').'.');
        }
    }

    private function registryTransition(ListingId $id, SubmitListing $useCase, ListingRevisionAllocatorV1 $allocator, ListingRevisionOperation $operation, string $intent, TransitionTrigger $trigger, TransitionOrigin $origin, DateTimeImmutable $at): void
    {
        $listing = app(ListingRegistry::class)->find($id);
        if ($listing?->version() >= 2) {
            return;
        }
        $revision = $allocator->allocate($id, $operation, ListingRevisionIntentId::fromString($intent));
        app(ListingPublicationAtomicTransaction::class)->run(
            static function () use ($useCase, $id, $revision, $trigger, $origin, $at): void {
                $useCase->execute($id, $revision, new TransitionEvidence(ActorId::fromString(self::OWNER), $trigger, null, $origin, $at));
            },
        );
    }

    private function lifecycleTransition(ListingId $id, ListingPublicationAction $action, SendToReview $useCase, ListingRevisionAllocatorV1 $allocator, ListingRevisionOperation $operation, string $intent, int $expected, TransitionTrigger $trigger, DateTimeImmutable $at): void
    {
        if (app(ListingRegistry::class)->find($id)?->version() >= 3) {
            return;
        }
        $revision = $allocator->allocate($id, $operation, ListingRevisionIntentId::fromString($intent));
        app(ListingPublicationAtomicTransaction::class)->run(function () use ($id, $action, $useCase, $revision, $expected, $trigger, $at): void {
            $this->expectWorkflow(app(ListingPublicationOrchestrator::class)->transition(new ListingPublicationOrchestrationRequest($id, $action, $expected))->status);
            $useCase->execute($id, $revision, new TransitionEvidence(ActorId::fromString('b0300000-0000-4000-8000-000000000099'), $trigger, null, TransitionOrigin::Moderation, $at));
        });
    }

    private function publish(ListingId $id, ListingRevisionAllocatorV1 $allocator, DateTimeImmutable $at): void
    {
        if (app(ListingRegistry::class)->find($id)?->version() >= 4) {
            return;
        }
        $revision = $allocator->allocate($id, ListingRevisionOperation::ApproveAndPublish, ListingRevisionIntentId::fromString('b0300000-0000-4000-8000-000000000033'));
        app(ListingPublicationAtomicTransaction::class)->run(function () use ($id, $revision, $at): void {
            $this->expectWorkflow(app(ListingPublicationOrchestrator::class)->transition(new ListingPublicationOrchestrationRequest($id, ListingPublicationAction::ApproveAndPublish, 3))->status);
            app(PublishListing::class)->execute($id, MediaCollectionId::fromString(self::COLLECTION), $revision, app(ListingPublicationExpirationPolicyV1::class)->expirationFor($at), new TransitionEvidence(ActorId::fromString('b0300000-0000-4000-8000-000000000099'), TransitionTrigger::FavorableReview, null, TransitionOrigin::Moderation, $at));
        });
    }

    private function expectWorkflow(ListingPublicationOrchestrationStatus $status): void
    {
        if (! in_array($status, [ListingPublicationOrchestrationStatus::Applied, ListingPublicationOrchestrationStatus::AlreadyApplied], true)) {
            throw new RuntimeException('The publication workflow did not converge.');
        }
    }

    private function materializeSources(DateTimeImmutable $at): void
    {
        $listingId = SearchListingId::fromString(self::LISTING);
        $revisions = new SourceRevisionSet(
            SourceRevision::create(SourceKind::Listing, 1, 'b0300000-0000-4000-8000-000000000041', $at),
            SourceRevision::create(SourceKind::Property, 1, 'b0300000-0000-4000-8000-000000000042', $at),
            SourceRevision::create(SourceKind::Media, 1, 'b0300000-0000-4000-8000-000000000043', $at),
        );
        app(PostgreSqlSearchDecisionWriter::class)->store(new SearchDecision(SearchIndexId::fromString('b0300000-0000-4000-8000-000000000044'), $listingId, 1, SearchProjection::derived(ProjectionState::Visible, SearchRank::fromInt(600), [new SearchFacet(SearchFacetKey::fromString('property.type'), SearchFacetValue::fromString('apartment'), SourceKind::Property)], $revisions)));

        $seoId = SeoListingId::fromString(self::LISTING);
        $coherence = 'b0300000-0000-4000-8000-000000000045';
        $revision = static fn (SeoSourceKind $kind, string $fact): SeoSourceRevision => SeoSourceRevision::create($kind, 1, $fact, $coherence, $at);
        $publishedAt = $at->modify('+4 minutes');
        app(PostgreSqlContentSeoSourceSnapshotWriter::class)->store(new ContentSeoSourceDecision(
            'b0300000-0000-4000-8000-000000000046',
            $seoId,
            1,
            new ListingSeoSource($seoId, ListingSeoState::Published, 'Appartement à vendre à Dakar', 'Annonce locale publiée avec transaction qualifiée.', self::CANONICAL, $revision(SeoSourceKind::Listing, 'b0300000-0000-4000-8000-000000000047'), $publishedAt, app(ListingPublicationExpirationPolicyV1::class)->expirationFor($publishedAt)->value),
            new SearchSeoSource($seoId, SearchSeoState::Public, $revision(SeoSourceKind::Search, 'b0300000-0000-4000-8000-000000000048')),
            new PropertySeoSource($seoId, PropertySeoState::Available, 'Appartement', 'Dakar', $revision(SeoSourceKind::Property, 'b0300000-0000-4000-8000-000000000049')),
            [new CanonicalHistoryEntry(CanonicalUrl::fromString('https://appart.sn/'.self::CANONICAL), CanonicalDisposition::Current, $publishedAt)],
            $publishedAt,
        ));
    }
}
