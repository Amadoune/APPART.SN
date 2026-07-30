<?php

namespace Tests\Unit\Contracts\PublicProjectionStore;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicListingProjectionState;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Support\PublicListingReadModelFixture;

abstract class PublicListingProjectionStoreContract extends TestCase
{
    private PublicListingProjectionStoreHarness $harness;

    private PublicListingProjectionWriter $writer;

    private PublicListingQuery $query;

    final protected function setUp(): void
    {
        $this->harness = $this->createHarness();
        $this->writer = $this->harness->writer();
        $this->query = $this->harness->query();
    }

    abstract protected function createHarness(): PublicListingProjectionStoreHarness;

    final public function test_unknown_canonical_is_absent(): void
    {
        self::assertNull($this->query->findByCanonicalPath('annonces/inconnue'));
    }

    final public function test_first_current_projection_is_applied_and_read_exactly(): void
    {
        $snapshot = $this->current();

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($snapshot));
        $actual = $this->query->findByCanonicalPath($snapshot->canonicalPath);
        self::assertEquals($snapshot->readModel, $actual);
        self::assertNotSame($snapshot->readModel, $actual);
    }

    final public function test_noindex_is_an_independent_seo_fact_and_can_remain_current(): void
    {
        $model = PublicListingReadModelFixture::make(indexable: false);
        $snapshot = PublicListingProjectionRecord::current($model->listingId, $this->canonical(), $model, $this->watermark(), $this->harness->activeGenerationId());

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($snapshot));
        self::assertSame('not_indexable', $this->query->findByCanonicalPath($snapshot->canonicalPath)?->indexability);
        self::assertSame('noindex, follow', $this->query->findByCanonicalPath($snapshot->canonicalPath)?->htmlRobotsDirective);
    }

    final public function test_identical_application_is_idempotent(): void
    {
        $snapshot = $this->current();

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($snapshot));
        self::assertSame(PublicProjectionWriteResult::AlreadyApplied, $this->writer->applyCurrent($snapshot));
    }

    final public function test_newer_watermark_replaces_same_current_projection(): void
    {
        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($this->current()));
        $newer = $this->current(watermark: $this->watermark(2));

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($newer));
        self::assertEquals($newer->watermark, $this->stored($newer)->watermark);
    }

    final public function test_obsolete_watermark_is_rejected_without_overwrite(): void
    {
        $newer = $this->current(watermark: $this->watermark(2));
        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($newer));

        self::assertSame(PublicProjectionWriteResult::RejectedObsolete, $this->writer->applyCurrent($this->current()));
        self::assertEquals($newer->watermark, $this->stored($newer)->watermark);
    }

    final public function test_incomparable_watermark_is_reported_as_divergent(): void
    {
        $first = $this->current(watermark: $this->watermark(listing: 2, property: 1));
        $crossed = $this->current(watermark: $this->watermark(listing: 1, property: 2));
        $this->writer->applyCurrent($first);

        self::assertSame(PublicProjectionWriteResult::DivergentWatermark, $this->writer->applyCurrent($crossed));
        self::assertEquals($first->watermark, $this->stored($first)->watermark);
    }

    final public function test_unversioned_public_source_blocks_promotion(): void
    {
        $incomplete = $this->current(watermark: $this->watermark(geography: null));

        self::assertSame(PublicProjectionWriteResult::IncompleteWatermark, $this->writer->applyCurrent($incomplete));
        self::assertNull($this->query->findByCanonicalPath($incomplete->canonicalPath));
    }

    final public function test_canonical_replacement_is_atomic_and_reserves_history(): void
    {
        $previous = $this->current();
        $replacement = $this->current(canonical: 'annonces/nouvelle-canonical', watermark: $this->watermark(2));
        $this->writer->applyCurrent($previous);

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->replaceCanonical($previous->canonicalPath, $replacement));
        self::assertNull($this->query->findByCanonicalPath($previous->canonicalPath));
        self::assertEquals($replacement->readModel, $this->query->findByCanonicalPath($replacement->canonicalPath));
        self::assertSame(PublicListingProjectionState::Historical, $this->stored($previous)->state);
    }

    final public function test_repeating_identical_canonical_replacement_is_idempotent(): void
    {
        $previous = $this->current();
        $replacement = $this->current(canonical: 'annonces/nouvelle-canonical', watermark: $this->watermark(2));
        $this->writer->applyCurrent($previous);
        $this->writer->replaceCanonical($previous->canonicalPath, $replacement);

        self::assertSame(PublicProjectionWriteResult::AlreadyApplied, $this->writer->replaceCanonical($previous->canonicalPath, $replacement));
    }

    final public function test_historical_canonical_cannot_be_reassigned(): void
    {
        $previous = $this->current();
        $replacement = $this->current(canonical: 'annonces/nouvelle-canonical', watermark: $this->watermark(2));
        $this->writer->applyCurrent($previous);
        $this->writer->replaceCanonical($previous->canonicalPath, $replacement);

        $intruder = $this->current(listingId: $this->otherListingId(), canonical: $previous->canonicalPath, watermark: $this->watermark(3));
        self::assertSame(PublicProjectionWriteResult::HistoricalReservationConflict, $this->writer->applyCurrent($intruder));
        self::assertNull($this->query->findByCanonicalPath($previous->canonicalPath));
    }

    final public function test_direct_canonical_change_requires_specialized_replacement_intent(): void
    {
        $this->writer->applyCurrent($this->current());

        self::assertSame(
            PublicProjectionWriteResult::CanonicalReplacementRequired,
            $this->writer->applyCurrent($this->current(canonical: 'annonces/autre-canonical', watermark: $this->watermark(2))),
        );
    }

    final public function test_two_listings_cannot_share_current_canonical_in_one_generation(): void
    {
        $owner = $this->current();
        $intruder = $this->current(listingId: $this->otherListingId());
        $this->writer->applyCurrent($owner);

        self::assertSame(PublicProjectionWriteResult::CanonicalCollision, $this->writer->applyCurrent($intruder));
        self::assertSame($owner->listingId, $this->query->findByCanonicalPath($owner->canonicalPath)?->listingId);
    }

    final public function test_failed_replacement_collision_preserves_both_existing_currents(): void
    {
        $first = $this->current();
        $second = $this->current(listingId: $this->otherListingId(), canonical: 'annonces/deja-prise');
        $this->writer->applyCurrent($first);
        $this->writer->applyCurrent($second);
        $replacement = $this->current(canonical: $second->canonicalPath, watermark: $this->watermark(2));

        self::assertSame(PublicProjectionWriteResult::CanonicalCollision, $this->writer->replaceCanonical($first->canonicalPath, $replacement));
        self::assertSame($first->listingId, $this->query->findByCanonicalPath($first->canonicalPath)?->listingId);
        self::assertSame($second->listingId, $this->query->findByCanonicalPath($second->canonicalPath)?->listingId);
    }

    final public function test_listing_id_is_never_a_public_query_fallback(): void
    {
        $snapshot = $this->current();
        $this->writer->applyCurrent($snapshot);

        self::assertNull($this->query->findByCanonicalPath($snapshot->listingId));
    }

    final public function test_newer_tombstone_is_applied_and_never_served(): void
    {
        $current = $this->current();
        $this->writer->applyCurrent($current);
        $tombstone = $this->tombstone(watermark: $this->watermark(2));

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyTombstone($tombstone));
        self::assertNull($this->query->findByCanonicalPath($current->canonicalPath));
        self::assertSame(PublicListingProjectionState::Tombstone, $this->stored($tombstone)->state);
    }

    final public function test_old_event_cannot_resurrect_after_tombstone(): void
    {
        $this->writer->applyCurrent($this->current());
        $this->writer->applyTombstone($this->tombstone(watermark: $this->watermark(2)));

        self::assertSame(PublicProjectionWriteResult::RejectedObsolete, $this->writer->applyCurrent($this->current()));
        self::assertNull($this->query->findByCanonicalPath($this->canonical()));
    }

    final public function test_identical_tombstone_is_idempotent(): void
    {
        $this->writer->applyCurrent($this->current());
        $tombstone = $this->tombstone(watermark: $this->watermark(2));
        $this->writer->applyTombstone($tombstone);

        self::assertSame(PublicProjectionWriteResult::AlreadyApplied, $this->writer->applyTombstone($tombstone));
    }

    final public function test_explicitly_newer_reconstruction_can_restore_same_canonical_after_tombstone(): void
    {
        $this->writer->applyCurrent($this->current());
        $this->writer->applyTombstone($this->tombstone(watermark: $this->watermark(2)));
        $restored = $this->current(watermark: $this->watermark(3));

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($restored));
        self::assertEquals($restored->readModel, $this->query->findByCanonicalPath($restored->canonicalPath));
    }

    final public function test_candidate_generation_is_invisible_to_public_query(): void
    {
        $candidate = $this->current(generation: $this->harness->candidateGenerationId());

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->writeCandidate($candidate));
        self::assertNull($this->query->findByCanonicalPath($candidate->canonicalPath));
        self::assertNotNull($this->stored($candidate));
    }

    final public function test_same_canonical_is_isolated_between_active_and_candidate_generations(): void
    {
        $active = $this->current();
        $candidate = $this->current(generation: $this->harness->candidateGenerationId());

        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->applyCurrent($active));
        self::assertSame(PublicProjectionWriteResult::Applied, $this->writer->writeCandidate($candidate));
        self::assertEquals($active->readModel, $this->query->findByCanonicalPath($active->canonicalPath));
    }

    final public function test_wrong_generation_is_rejected_without_ambiguity(): void
    {
        $candidate = $this->current(generation: $this->harness->candidateGenerationId());
        $active = $this->current();

        self::assertSame(PublicProjectionWriteResult::GenerationMismatch, $this->writer->applyCurrent($candidate));
        self::assertSame(PublicProjectionWriteResult::GenerationMismatch, $this->writer->writeCandidate($active));
    }

    final public function test_snapshot_watermark_and_read_model_are_immutable_and_fake_does_not_mutate_input(): void
    {
        $snapshot = $this->current();
        $before = serialize($snapshot);
        $this->writer->applyCurrent($snapshot);

        self::assertTrue((new ReflectionClass($snapshot))->isReadOnly());
        self::assertTrue((new ReflectionClass($snapshot->watermark))->isReadOnly());
        self::assertTrue((new ReflectionClass($snapshot->readModel))->isReadOnly());
        self::assertSame($before, serialize($snapshot));
        self::assertNotSame($snapshot, $this->stored($snapshot));
    }

    private function current(
        ?string $listingId = null,
        ?string $canonical = null,
        ?PublicProjectionWatermark $watermark = null,
        ?PublicProjectionGenerationId $generation = null,
    ): PublicListingProjectionRecord {
        $listingId ??= $this->listingId();
        $canonical ??= $this->canonical();
        $model = PublicListingReadModelFixture::make(listingId: $listingId, canonicalUrl: 'https://appart.sn/'.$canonical);

        return PublicListingProjectionRecord::current($listingId, $canonical, $model, $watermark ?? $this->watermark(), $generation ?? $this->harness->activeGenerationId());
    }

    private function tombstone(?PublicProjectionWatermark $watermark = null): PublicListingProjectionRecord
    {
        return PublicListingProjectionRecord::tombstone($this->listingId(), $this->canonical(), $watermark ?? $this->watermark(), $this->harness->activeGenerationId());
    }

    private function watermark(
        int $listing = 1,
        int $property = 1,
        int $media = 1,
        int $search = 1,
        int $contentSeo = 1,
        ?int $geography = 1,
        ?int $publicMedia = 1,
    ): PublicProjectionWatermark {
        return new PublicProjectionWatermark($listing, $property, $media, $search, $contentSeo, $geography, $publicMedia);
    }

    private function stored(PublicListingProjectionRecord $snapshot): PublicListingProjectionRecord
    {
        $stored = $this->harness->snapshot($snapshot->generationId, $snapshot->canonicalPath);
        self::assertNotNull($stored);

        return $stored;
    }

    private function listingId(): string
    {
        return '91000000-0000-4000-8000-000000000001';
    }

    private function otherListingId(): string
    {
        return '91000000-0000-4000-8000-000000000002';
    }

    private function canonical(): string
    {
        return 'annonces/appartement-moderne-dakar';
    }
}
