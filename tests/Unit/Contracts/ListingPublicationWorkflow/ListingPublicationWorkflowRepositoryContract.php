<?php

namespace Tests\Unit\Contracts\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDecisionStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class ListingPublicationWorkflowRepositoryContract extends TestCase
{
    abstract protected function repository(): ListingPublicationWorkflowStore;

    public function test_missing_initialization_read_and_idempotence_are_explicit(): void
    {
        $id = $this->listingId(1);
        self::assertSame(ListingPublicationPersistenceReadStatus::Missing, $this->repository()->read($id)->status);
        self::assertSame(ListingPublicationPersistenceWriteResult::Applied, $this->repository()->initialize($id, ListingPublicationState::Draft));
        self::assertSame(ListingPublicationPersistenceWriteResult::AlreadyApplied, $this->repository()->initialize($id, ListingPublicationState::Draft));
        $snapshot = $this->repository()->read($id)->snapshot;
        self::assertSame(ListingPublicationState::Draft, $snapshot?->state);
        self::assertSame(1, $snapshot?->version);
    }

    #[DataProvider('states')]
    public function test_every_state_round_trips(ListingPublicationState $state, int $number): void
    {
        $id = $this->listingId($number);
        self::assertSame(ListingPublicationPersistenceWriteResult::Applied, $this->repository()->initialize($id, $state));
        self::assertSame($state, $this->repository()->read($id)->snapshot?->state);
    }

    /** @return iterable<array{ListingPublicationState, int}> */
    public static function states(): iterable
    {
        foreach (ListingPublicationState::cases() as $index => $state) {
            yield $state->value => [$state, $index + 10];
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_every_allowed_workflow_transition_is_persisted(
        ListingPublicationState $from,
        ListingPublicationAction $action,
        int $number,
    ): void {
        $id = $this->listingId($number);
        $decision = (new ListingPublicationWorkflow)->decide($from, $action);
        self::assertSame(ListingPublicationDecisionStatus::Allowed, $decision->status);
        self::assertNotNull($decision->transition);
        $this->repository()->initialize($id, $from);

        self::assertSame(ListingPublicationPersistenceWriteResult::Applied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame(ListingPublicationPersistenceWriteResult::AlreadyApplied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame($decision->transition->to, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(2, $this->repository()->read($id)->snapshot?->version);
    }

    /** @return iterable<array{ListingPublicationState, ListingPublicationAction, int}> */
    public static function allowedTransitions(): iterable
    {
        $number = 100;
        foreach (ListingPublicationState::cases() as $state) {
            foreach (ListingPublicationAction::cases() as $action) {
                if ((new ListingPublicationWorkflow)->decide($state, $action)->status === ListingPublicationDecisionStatus::Allowed) {
                    yield $state->value.'>'.$action->value => [$state, $action, $number++];
                }
            }
        }
    }

    public function test_forbidden_transition_version_gap_and_state_conflict_are_rejected(): void
    {
        $id = $this->listingId(500);
        $this->repository()->initialize($id, ListingPublicationState::Draft);
        self::assertSame(
            ListingPublicationPersistenceWriteResult::TransitionRejected,
            $this->repository()->append($id, new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Expired, ListingPublicationAction::Expire), 2),
        );
        self::assertSame(
            ListingPublicationPersistenceWriteResult::RejectedVersion,
            $this->repository()->append($id, new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit), 3),
        );
        self::assertSame(
            ListingPublicationPersistenceWriteResult::StateConflict,
            $this->repository()->append($id, new ListingPublicationTransition(ListingPublicationState::Published, ListingPublicationState::Expired, ListingPublicationAction::Expire), 2),
        );
        self::assertSame(ListingPublicationState::Draft, $this->repository()->read($id)->snapshot?->state);
    }

    protected function listingId(int $number): ListingId
    {
        return ListingId::fromString(sprintf('98000000-0000-4000-8000-%012d', $number));
    }
}
