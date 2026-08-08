<?php

namespace Tests\Unit\ListingLifecycle\ContactabilityBoundary;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ContactabilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ListingContactabilityDecisionV1;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\OwnerListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OwnerListingContactabilityReaderV1Test extends TestCase
{
    #[Test]
    public function published_is_the_only_contactable_owner_state(): void
    {
        $listingId = $this->listingId();

        self::assertSame(
            ListingContactabilityDecisionV1::Contactable,
            $this->reader(OwnerContactabilityWorkflowStoreStub::found(
                $listingId,
                ListingPublicationState::Published,
            ))->read($listingId, $this->observedAt()),
        );

        foreach ([
            ListingPublicationState::Draft,
            ListingPublicationState::Submitted,
            ListingPublicationState::UnderReview,
            ListingPublicationState::ChangesRequested,
            ListingPublicationState::Suspended,
            ListingPublicationState::Expired,
            ListingPublicationState::Withdrawn,
            ListingPublicationState::Rejected,
            ListingPublicationState::Archived,
        ] as $state) {
            self::assertSame(
                ListingContactabilityDecisionV1::NotContactable,
                $this->reader(OwnerContactabilityWorkflowStoreStub::found(
                    $listingId,
                    $state,
                ))->read($listingId, $this->observedAt()),
                $state->value,
            );
        }
    }

    #[Test]
    public function missing_corrupted_and_unavailable_remain_distinct_and_fail_closed(): void
    {
        $listingId = $this->listingId();

        self::assertSame(
            ListingContactabilityDecisionV1::Missing,
            $this->reader(OwnerContactabilityWorkflowStoreStub::result(
                ListingPublicationPersistenceReadResult::missing($listingId),
            ))->read($listingId, $this->observedAt()),
        );
        self::assertSame(
            ListingContactabilityDecisionV1::Corrupted,
            $this->reader(OwnerContactabilityWorkflowStoreStub::result(
                ListingPublicationPersistenceReadResult::corrupted($listingId),
            ))->read($listingId, $this->observedAt()),
        );
        self::assertSame(
            ListingContactabilityDecisionV1::DependencyUnavailable,
            $this->reader(OwnerContactabilityWorkflowStoreStub::unavailable())
                ->read($listingId, $this->observedAt()),
        );
    }

    #[Test]
    public function same_identity_and_observation_are_deterministic_and_read_only(): void
    {
        $listingId = $this->listingId();
        $observedAt = $this->observedAt();
        $store = OwnerContactabilityWorkflowStoreStub::found(
            $listingId,
            ListingPublicationState::Published,
        );
        $reader = $this->reader($store);

        $first = $reader->read($listingId, $observedAt);
        $second = $reader->read($listingId, $observedAt);

        self::assertSame($first, $second);
        self::assertSame(2, $store->reads);
        self::assertSame(0, $store->mutations);
    }

    private function reader(ListingPublicationWorkflowStore $store): OwnerListingContactabilityReaderV1
    {
        return new OwnerListingContactabilityReaderV1($store);
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('54a10000-0000-4000-8000-000000000001');
    }

    private function observedAt(): ContactabilityObservedAt
    {
        return new ContactabilityObservedAt(new DateTimeImmutable('2026-07-31T12:00:00+00:00'));
    }
}

final class OwnerContactabilityWorkflowStoreStub implements ListingPublicationWorkflowStore
{
    public int $reads = 0;

    public int $mutations = 0;

    private function __construct(
        private ?ListingPublicationPersistenceReadResult $result,
        private bool $unavailable,
    ) {}

    public static function found(ListingId $listingId, ListingPublicationState $state): self
    {
        return self::result(ListingPublicationPersistenceReadResult::found(
            new ListingPublicationStoredState($listingId, $state, 1),
        ));
    }

    public static function result(ListingPublicationPersistenceReadResult $result): self
    {
        return new self($result, false);
    }

    public static function unavailable(): self
    {
        return new self(null, true);
    }

    public function initialize(ListingId $listingId, ListingPublicationState $state): ListingPublicationPersistenceWriteResult
    {
        $this->mutations++;

        throw new LogicException('A read-only boundary cannot initialize a workflow.');
    }

    public function append(ListingId $listingId, ListingPublicationTransition $transition, int $version): ListingPublicationPersistenceWriteResult
    {
        $this->mutations++;

        throw new LogicException('A read-only boundary cannot append a transition.');
    }

    public function read(ListingId $listingId): ListingPublicationPersistenceReadResult
    {
        $this->reads++;
        if ($this->unavailable) {
            throw new RuntimeException('Owner source unavailable.');
        }

        return $this->result ?? throw new LogicException('Missing test result.');
    }
}
