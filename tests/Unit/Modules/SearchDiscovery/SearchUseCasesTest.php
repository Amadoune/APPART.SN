<?php

namespace Tests\Unit\Modules\SearchDiscovery;

use Appart\Modules\SearchDiscovery\Application\UseCase\IndexListing;
use Appart\Modules\SearchDiscovery\Application\UseCase\ProjectionSources;
use Appart\Modules\SearchDiscovery\Application\UseCase\UpdateIndex;
use Appart\Modules\SearchDiscovery\Domain\Exception\ConcurrentSearchIndexModification;
use Appart\Modules\SearchDiscovery\Domain\Exception\DuplicateProjectionFact;
use Appart\Modules\SearchDiscovery\Domain\Exception\ListingDocumentConflict;
use Appart\Modules\SearchDiscovery\Domain\Exception\ProjectionSourceUnavailable;
use Appart\Modules\SearchDiscovery\Domain\Exception\SearchDocumentIdentityConflict;
use Appart\Modules\SearchDiscovery\Domain\Exception\StaleProjection;
use Appart\Modules\SearchDiscovery\Domain\Policy\ProjectionLifecyclePolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFacetPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFreshnessPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchVisibilityPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use Tests\Unit\Modules\SearchDiscovery\Support\FakeListingCatalog;
use Tests\Unit\Modules\SearchDiscovery\Support\FakeMediaCatalog;
use Tests\Unit\Modules\SearchDiscovery\Support\FakePropertyCatalog;
use Tests\Unit\Modules\SearchDiscovery\Support\FakeSearchIndexRegistry;

final class SearchUseCasesTest extends SearchDomainTestCase
{
    private FakeSearchIndexRegistry $registry;

    private FakeListingCatalog $listings;

    private FakePropertyCatalog $properties;

    private FakeMediaCatalog $media;

    private IndexListing $index;

    private UpdateIndex $update;

    private SearchIndexId $indexId;

    private SearchDocumentId $documentId;

    protected function setUp(): void
    {
        $this->registry = new FakeSearchIndexRegistry;
        $this->listings = new FakeListingCatalog;
        $this->properties = new FakePropertyCatalog;
        $this->media = new FakeMediaCatalog;
        $sources = new ProjectionSources($this->listings, $this->properties, $this->media);
        $projections = new SearchProjectionPolicy(new SearchVisibilityPolicy, new SearchFacetPolicy);
        $this->index = new IndexListing($this->registry, $sources, $projections);
        $this->update = new UpdateIndex($this->registry, $sources, $projections, new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);
        $this->indexId = SearchIndexId::fromString('90000000-0000-4000-8000-000000000001');
        $this->documentId = SearchDocumentId::fromString('90000000-0000-4000-8000-000000000002');
        $this->provide(1);
    }

    public function test_initial_index_is_stored_as_event_free_detached_snapshot(): void
    {
        $returned = $this->index->execute($this->indexId, $this->documentId, $this->listingId(), $this->at(1));
        $stored = $this->registry->find($this->indexId);

        self::assertNotNull($stored);
        self::assertSame([], $stored->releaseEvents());
        self::assertNotEmpty($returned->releaseEvents());
    }

    public function test_missing_source_prevents_initial_projection(): void
    {
        $missing = $this->listingId(99);
        $this->expectException(ProjectionSourceUnavailable::class);
        try {
            $this->index->execute($this->indexId, $this->documentId, $missing, $this->at(1));
        } finally {
            self::assertNull($this->registry->find($this->indexId));
        }
    }

    public function test_update_uses_newer_sources_and_derives_visibility(): void
    {
        $this->create();
        $this->provide(2, ListingSearchState::TemporarilyUnavailable);
        $this->update->execute($this->indexId, $this->at(2));

        self::assertSame(ProjectionState::Hidden, $this->registry->find($this->indexId)?->document()->state());
    }

    public function test_duplicate_delivery_is_idempotent_and_keeps_snapshot(): void
    {
        $this->create();
        $this->expectException(DuplicateProjectionFact::class);
        try {
            $this->update->execute($this->indexId, $this->at(2));
        } finally {
            self::assertSame(1, $this->registry->find($this->indexId)?->version());
        }
    }

    public function test_late_obsolete_delivery_cannot_overwrite_current_snapshot(): void
    {
        $this->provide(2);
        $this->create();
        $this->provide(1, ListingSearchState::TemporarilyUnavailable);

        $this->expectException(StaleProjection::class);
        try {
            $this->update->execute($this->indexId, $this->at(3));
        } finally {
            self::assertSame(ProjectionState::Visible, $this->registry->find($this->indexId)?->document()->state());
        }
    }

    public function test_failed_save_rolls_back_visible_mutation(): void
    {
        $this->create();
        $this->provide(2, ListingSearchState::TemporarilyUnavailable);
        $this->registry->failNextWrite();

        $this->expectException(ConcurrentSearchIndexModification::class);
        try {
            $this->update->execute($this->indexId, $this->at(2));
        } finally {
            self::assertSame(ProjectionState::Visible, $this->registry->find($this->indexId)?->document()->state());
            self::assertSame(1, $this->registry->find($this->indexId)?->version());
        }
    }

    public function test_failed_add_leaves_no_identity_reservation(): void
    {
        $this->registry->failNextWrite();
        try {
            $this->create();
            self::fail('The first add must fail.');
        } catch (ConcurrentSearchIndexModification) {
            self::assertNull($this->registry->findByListing($this->listingId()));
        }

        $this->create();
        self::assertNotNull($this->registry->findByListing($this->listingId()));
    }

    public function test_two_bounded_roots_cannot_claim_same_listing(): void
    {
        $this->create();
        $this->expectException(ListingDocumentConflict::class);
        $this->index->execute(
            SearchIndexId::fromString('90000000-0000-4000-8000-000000000003'),
            SearchDocumentId::fromString('90000000-0000-4000-8000-000000000004'),
            $this->listingId(),
            $this->at(2),
        );
    }

    public function test_two_bounded_roots_cannot_claim_same_document_identity(): void
    {
        $this->create();
        $this->provide(1, id: $this->listingId(2));
        $this->expectException(SearchDocumentIdentityConflict::class);
        $this->index->execute(
            SearchIndexId::fromString('90000000-0000-4000-8000-000000000003'),
            $this->documentId,
            $this->listingId(2),
            $this->at(2),
        );
    }

    public function test_remove_then_republish_converges_to_rebuilt_visible_projection(): void
    {
        $this->create();
        $this->provide(2, ListingSearchState::Terminal);
        $this->update->execute($this->indexId, $this->at(2));
        $this->provide(3, ListingSearchState::Published);
        $this->update->execute($this->indexId, $this->at(3));

        $stored = $this->registry->find($this->indexId);
        self::assertSame(ProjectionState::Visible, $stored?->document()->state());
        self::assertSame(3, $stored?->version());
    }

    private function create(): void
    {
        $this->index->execute($this->indexId, $this->documentId, $this->listingId(), $this->at(1));
    }

    private function provide(int $version, ListingSearchState $state = ListingSearchState::Published, ?ListingId $id = null): void
    {
        [$listing, $property, $media] = $this->sources($version, $state, id: $id);
        $this->listings->set($listing->listingId, $listing);
        $this->properties->set($property->listingId, $property);
        $this->media->set($media->listingId, $media);
    }
}
