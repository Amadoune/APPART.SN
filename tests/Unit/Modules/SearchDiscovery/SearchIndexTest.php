<?php

namespace Tests\Unit\Modules\SearchDiscovery;

use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentRebuilt;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentRemoved;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentUpdated;
use Appart\Modules\SearchDiscovery\Domain\Exception\DuplicateProjectionFact;
use Appart\Modules\SearchDiscovery\Domain\Exception\InconsistentProjectionSources;
use Appart\Modules\SearchDiscovery\Domain\Exception\StaleProjection;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchIndex;
use Appart\Modules\SearchDiscovery\Domain\Policy\ProjectionLifecyclePolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFreshnessPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;

final class SearchIndexTest extends SearchDomainTestCase
{
    public function test_root_is_bounded_to_exactly_one_listing_and_document(): void
    {
        $index = $this->index();

        self::assertTrue($index->listingId()->equals($this->listingId()));
        self::assertSame('90000000-0000-4000-8000-000000000002', $index->document()->id->value);
        self::assertSame(1, $index->version());
    }

    public function test_newer_projection_reindexes_without_global_contention(): void
    {
        $index = $this->index();
        $index->releaseEvents();
        $index->synchronize($this->projection($this->sources(2)), $this->at(2), new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);

        self::assertSame(2, $index->version());
        self::assertInstanceOf(SearchDocumentUpdated::class, $index->releaseEvents()[0]);
    }

    public function test_duplicate_fact_is_idempotently_rejected_without_mutation(): void
    {
        $index = $this->index();
        $index->releaseEvents();

        try {
            $index->synchronize($this->projection($this->sources(1)), $this->at(2), new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);
            self::fail('Duplicate facts must be ignored.');
        } catch (DuplicateProjectionFact) {
            self::assertSame(1, $index->version());
            self::assertSame([], $index->releaseEvents());
        }
    }

    public function test_obsolete_projection_never_overwrites_newer_projection(): void
    {
        $index = $this->index(2);
        $index->releaseEvents();

        $this->expectException(StaleProjection::class);
        try {
            $index->synchronize($this->projection($this->sources(1)), $this->at(3), new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);
        } finally {
            self::assertSame(1, $index->version());
        }
    }

    public function test_mixed_source_order_is_rejected(): void
    {
        $index = $this->index(2);
        [$listing] = $this->sources(3);
        [, $property, $media] = $this->sources(1);

        $this->expectException(InconsistentProjectionSources::class);
        $index->synchronize($this->projection([$listing, $property, $media]), $this->at(4), new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);
    }

    public function test_removed_projection_can_be_rebuilt_after_republication(): void
    {
        $index = $this->index();
        $index->releaseEvents();
        $index->synchronize($this->projection($this->sources(2, ListingSearchState::Terminal)), $this->at(2), new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);
        self::assertSame(ProjectionState::Removed, $index->document()->state());
        self::assertInstanceOf(SearchDocumentRemoved::class, array_values(array_filter($index->releaseEvents(), fn ($event) => $event instanceof SearchDocumentRemoved))[0]);

        $index->synchronize($this->projection($this->sources(3)), $this->at(3), new SearchFreshnessPolicy, new ProjectionLifecyclePolicy);
        self::assertSame(ProjectionState::Visible, $index->document()->state());
        self::assertInstanceOf(SearchDocumentRebuilt::class, array_values(array_filter($index->releaseEvents(), fn ($event) => $event instanceof SearchDocumentRebuilt))[0]);
    }

    public function test_release_events_prevents_replay(): void
    {
        $index = $this->index();
        self::assertNotEmpty($index->releaseEvents());
        self::assertSame([], $index->releaseEvents());
    }

    private function index(int $version = 1): SearchIndex
    {
        return SearchIndex::create(
            SearchIndexId::fromString('90000000-0000-4000-8000-000000000001'),
            SearchDocumentId::fromString('90000000-0000-4000-8000-000000000002'),
            $this->listingId(),
            $this->projection($this->sources($version)),
            $this->at($version),
        );
    }
}
