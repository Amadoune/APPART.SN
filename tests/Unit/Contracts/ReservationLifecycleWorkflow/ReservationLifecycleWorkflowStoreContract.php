<?php

namespace Tests\Unit\Contracts\ReservationLifecycleWorkflow;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflowResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceWriteResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class ReservationLifecycleWorkflowStoreContract extends TestCase
{
    abstract protected function repository(): ReservationLifecycleWorkflowStore;

    public function test_missing_initialization_read_and_idempotence_are_explicit(): void
    {
        $id = $this->reservationId(1);
        self::assertSame(ReservationLifecyclePersistenceReadStatus::Missing, $this->repository()->read($id)->status);
        self::assertSame(ReservationLifecyclePersistenceWriteResult::Applied, $this->repository()->initialize($id, ReservationLifecycleState::Draft));
        self::assertSame(ReservationLifecyclePersistenceWriteResult::AlreadyApplied, $this->repository()->initialize($id, ReservationLifecycleState::Draft));
        self::assertSame(ReservationLifecycleState::Draft, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(1, $this->repository()->read($id)->snapshot?->version);
    }

    #[DataProvider('states')]
    public function test_every_state_round_trips(ReservationLifecycleState $state, int $number): void
    {
        $id = $this->reservationId($number);
        self::assertSame(ReservationLifecyclePersistenceWriteResult::Applied, $this->repository()->initialize($id, $state));
        self::assertSame($state, $this->repository()->read($id)->snapshot?->state);
    }

    /** @return iterable<array{ReservationLifecycleState,int}> */
    public static function states(): iterable
    {
        foreach (ReservationLifecycleState::cases() as $index => $state) {
            yield $state->value => [$state, $index + 10];
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_every_certified_transition_is_persisted(ReservationLifecycleState $from, ReservationLifecycleAction $action, int $number): void
    {
        $id = $this->reservationId($number);
        $decision = (new ReservationLifecycleWorkflow)->decide($from, $action);
        self::assertSame(ReservationLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertNotNull($decision->transition);
        $this->repository()->initialize($id, $from);

        self::assertSame(ReservationLifecyclePersistenceWriteResult::Applied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame(ReservationLifecyclePersistenceWriteResult::AlreadyApplied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame($decision->transition->to, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(2, $this->repository()->read($id)->snapshot?->version);
    }

    /** @return iterable<array{ReservationLifecycleState,ReservationLifecycleAction,int}> */
    public static function allowedTransitions(): iterable
    {
        $number = 100;
        foreach (ReservationLifecycleState::cases() as $state) {
            foreach (ReservationLifecycleAction::cases() as $action) {
                if ((new ReservationLifecycleWorkflow)->decide($state, $action)->result === ReservationLifecycleWorkflowResult::Allowed) {
                    yield $state->value.'>'.$action->value => [$state, $action, $number++];
                }
            }
        }
    }

    public function test_forbidden_transition_version_gap_and_state_conflict_are_rejected(): void
    {
        $id = $this->reservationId(500);
        $this->repository()->initialize($id, ReservationLifecycleState::Draft);
        self::assertSame(
            ReservationLifecyclePersistenceWriteResult::TransitionRejected,
            $this->repository()->append($id, new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Confirm), 2),
        );
        self::assertSame(
            ReservationLifecyclePersistenceWriteResult::RejectedVersion,
            $this->repository()->append($id, new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit), 3),
        );
        self::assertSame(
            ReservationLifecyclePersistenceWriteResult::StateConflict,
            $this->repository()->append($id, new ReservationLifecycleTransition(ReservationLifecycleState::Requested, ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Confirm), 2),
        );
        self::assertSame(ReservationLifecycleState::Draft, $this->repository()->read($id)->snapshot?->state);
    }

    protected function reservationId(int $number): ReservationId
    {
        return ReservationId::fromString(sprintf('98000000-0000-4000-8000-%012d', $number));
    }
}
