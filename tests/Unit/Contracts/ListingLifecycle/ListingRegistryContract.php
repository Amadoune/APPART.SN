<?php

namespace Tests\Unit\Contracts\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingIdConflict;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingViolation;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class ListingRegistryContract extends TestCase
{
    private ListingRegistryHarness $harness;

    private ListingRegistry $registry;

    final protected function setUp(): void
    {
        $this->harness = $this->createHarness();
        $this->registry = $this->harness->freshRegistry();
    }

    abstract protected function createHarness(): ListingRegistryHarness;

    final public function test_unknown_identity_returns_explicit_absence(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
    }

    final public function test_add_then_find_restores_the_observable_root(): void
    {
        $expected = $this->harness->listingWithHistory();
        $this->registry->add($expected);
        $actual = $this->requiredFind($expected);

        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->propertyId()->value, $actual->propertyId()->value);
        self::assertSame($expected->status(), $actual->status());
        self::assertSame($expected->version(), $actual->version());
        self::assertSame($this->revisionFacts($expected), $this->revisionFacts($actual));
    }

    final public function test_reads_are_detached_and_independent(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        $first = $this->requiredFind($listing);
        $second = $this->requiredFind($listing);
        $this->harness->mutate($first);

        self::assertNotSame($first, $second);
        self::assertSame(ListingStatus::Draft, $second->status());
        self::assertSame(ListingStatus::Draft, $this->requiredFind($listing)->status());
    }

    final public function test_reloaded_root_has_no_residual_or_replayed_events(): void
    {
        $listing = $this->harness->listingWithHistory();
        $this->registry->add($listing);

        self::assertSame([], $this->requiredFind($listing)->releaseEvents());
        self::assertSame([], $this->requiredFind($listing)->releaseEvents());
    }

    final public function test_successful_writes_do_not_clear_caller_events(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        self::assertNotEmpty($listing->releaseEvents());

        $loaded = $this->requiredFind($listing);
        $this->harness->mutate($loaded);
        $this->registry->save($loaded, 0);
        self::assertNotEmpty($loaded->releaseEvents());
    }

    final public function test_duplicate_identity_is_atomic_and_preserves_the_original(): void
    {
        $original = $this->harness->archivedListing();
        $this->registry->add($original);

        try {
            $this->registry->add($this->harness->minimalListing());
            self::fail('The permanent ListingId reservation must reject reuse.');
        } catch (ListingIdConflict) {
            self::assertSame(ListingStatus::Archived, $this->requiredFind($original)->status());
        }
    }

    final public function test_save_requires_the_exact_expected_version(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        $loaded = $this->requiredFind($listing);
        $this->harness->mutate($loaded);
        $this->registry->save($loaded, 0);

        self::assertSame(1, $this->requiredFind($listing)->version());
        self::assertSame(ListingStatus::Submitted, $this->requiredFind($listing)->status());
    }

    final public function test_stale_and_absent_saves_use_the_concurrency_error(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        $winner = $this->requiredFind($listing);
        $stale = $this->requiredFind($listing);
        $this->harness->mutate($winner);
        $this->harness->mutate($stale);
        $this->registry->save($winner, 0);

        try {
            $this->registry->save($stale, 0);
            self::fail('A stale version must fail.');
        } catch (ConcurrentListingModification) {
            $absent = $this->harness->minimalListing($this->harness->distinctId());
            $this->harness->mutate($absent);
            $this->expectException(ConcurrentListingModification::class);
            $this->registry->save($absent, 0);
        }
    }

    final public function test_registry_never_invents_a_version(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        $this->harness->mutate($listing);
        $this->registry->save($listing, 0);

        self::assertSame($listing->version(), $this->requiredFind($listing)->version());
    }

    final public function test_failed_save_rolls_back_visibility_and_preserves_caller_events(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        $loaded = $this->requiredFind($listing);
        $this->harness->mutate($loaded);
        $this->harness->failNextWrite($this->registry);

        try {
            $this->registry->save($loaded, 0);
            self::fail('The deterministic write failure must be visible.');
        } catch (ConcurrentListingModification) {
            self::assertSame(ListingStatus::Draft, $this->requiredFind($listing)->status());
            self::assertSame(0, $this->requiredFind($listing)->version());
            self::assertNotEmpty($loaded->releaseEvents());
        }
    }

    final public function test_append_only_revision_order_and_evidence_are_preserved(): void
    {
        $listing = $this->harness->listingWithHistory();
        $this->registry->add($listing);
        $revisions = $this->requiredFind($listing)->revisions();

        self::assertSame([ListingStatus::Draft, ListingStatus::Submitted, ListingStatus::UnderReview], array_map(static fn ($revision): ListingStatus => $revision->status, $revisions));
        self::assertSame([0, 1, 2], array_map(static fn ($revision): int => (int) $revision->occurredAt->format('i'), $revisions));
        self::assertSame(['actor:listing-contract', 'actor:listing-contract', 'actor:listing-contract'], array_map(static fn ($revision): string => $revision->actorId->value, $revisions));
    }

    final public function test_duplicate_local_revision_is_rejected_without_registry_mutation(): void
    {
        $listing = $this->harness->minimalListing();
        $this->registry->add($listing);
        $loaded = $this->requiredFind($listing);

        try {
            $loaded->submit($loaded->revisions()[0]->id, $this->evidence(), new ListingTransitionPolicy, PropertyAvailability::Eligible);
            self::fail('A ListingRevisionId is unique inside its root.');
        } catch (ListingViolation) {
            self::assertSame(ListingStatus::Draft, $this->requiredFind($listing)->status());
            self::assertCount(1, $this->requiredFind($listing)->revisions());
        }
    }

    final public function test_archived_state_remains_terminal_after_reload(): void
    {
        $listing = $this->harness->archivedListing();
        $this->registry->add($listing);
        $loaded = $this->requiredFind($listing);

        $this->expectException(ListingViolation::class);
        $loaded->submit(ListingRevisionId::fromString('32000000-0000-4000-8000-000000000099'), $this->evidence(), new ListingTransitionPolicy, PropertyAvailability::Eligible);
    }

    final public function test_each_scenario_starts_with_a_fresh_empty_registry(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
        self::assertNull($this->registry->find($this->harness->distinctId()));
    }

    /** @return list<array<string, string|null>> */
    private function revisionFacts(Listing $listing): array
    {
        return array_map(static fn ($revision): array => [
            'id' => $revision->id->value,
            'previous' => $revision->previousStatus?->value,
            'status' => $revision->status->value,
            'actor' => $revision->actorId->value,
            'trigger' => $revision->trigger->value,
            'reason' => $revision->reason->value,
            'origin' => $revision->origin->value,
            'occurred_at' => $revision->occurredAt->format(DATE_ATOM),
        ], $listing->revisions());
    }

    private function requiredFind(Listing $listing): Listing
    {
        $loaded = $this->registry->find($listing->id());
        self::assertNotNull($loaded);

        return $loaded;
    }

    private function evidence(): TransitionEvidence
    {
        return new TransitionEvidence(
            ActorId::fromString('actor:listing-contract'),
            TransitionTrigger::SubmissionConfirmed,
            TransitionReason::fromString('Fixed duplicate revision evidence.'),
            TransitionOrigin::Advertiser,
            new DateTimeImmutable('2026-07-17T10:01:00+00:00'),
        );
    }
}
