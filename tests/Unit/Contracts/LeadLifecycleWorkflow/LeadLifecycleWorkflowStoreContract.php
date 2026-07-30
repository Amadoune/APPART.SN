<?php

namespace Tests\Unit\Contracts\LeadLifecycleWorkflow;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflowResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceWriteResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class LeadLifecycleWorkflowStoreContract extends TestCase
{
    abstract protected function repository(): LeadLifecycleWorkflowStore;

    public function test_missing_initialization_read_and_idempotence_are_explicit(): void
    {
        $id = $this->leadId(1);
        self::assertSame(LeadLifecyclePersistenceReadStatus::Missing, $this->repository()->read($id)->status);
        self::assertSame(LeadLifecyclePersistenceWriteResult::Applied, $this->repository()->initialize($id, LeadLifecycleState::Created));
        self::assertSame(LeadLifecyclePersistenceWriteResult::AlreadyApplied, $this->repository()->initialize($id, LeadLifecycleState::Created));
        self::assertSame(LeadLifecycleState::Created, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(1, $this->repository()->read($id)->snapshot?->version);
    }

    #[DataProvider('states')]
    public function test_every_state_round_trips(LeadLifecycleState $state, int $number): void
    {
        $id = $this->leadId($number);
        self::assertSame(LeadLifecyclePersistenceWriteResult::Applied, $this->repository()->initialize($id, $state));
        self::assertSame($state, $this->repository()->read($id)->snapshot?->state);
    }

    /** @return iterable<array{LeadLifecycleState,int}> */
    public static function states(): iterable
    {
        foreach (LeadLifecycleState::cases() as $index => $state) {
            yield $state->value => [$state, $index + 10];
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_every_certified_transition_is_persisted(LeadLifecycleState $from, LeadLifecycleAction $action, int $number): void
    {
        $id = $this->leadId($number);
        $decision = (new LeadLifecycleWorkflow)->decide($from, $action);
        self::assertSame(LeadLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertNotNull($decision->transition);
        $this->repository()->initialize($id, $from);

        self::assertSame(LeadLifecyclePersistenceWriteResult::Applied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame(LeadLifecyclePersistenceWriteResult::AlreadyApplied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame($decision->transition->to, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(2, $this->repository()->read($id)->snapshot?->version);
    }

    /** @return iterable<array{LeadLifecycleState,LeadLifecycleAction,int}> */
    public static function allowedTransitions(): iterable
    {
        $number = 100;
        foreach (LeadLifecycleState::cases() as $state) {
            foreach (LeadLifecycleAction::cases() as $action) {
                if ((new LeadLifecycleWorkflow)->decide($state, $action)->result === LeadLifecycleWorkflowResult::Allowed) {
                    yield $state->value.'>'.$action->value => [$state, $action, $number++];
                }
            }
        }
    }

    public function test_forbidden_transition_version_gap_and_state_conflict_are_rejected(): void
    {
        $id = $this->leadId(500);
        $this->repository()->initialize($id, LeadLifecycleState::Created);
        self::assertSame(
            LeadLifecyclePersistenceWriteResult::TransitionRejected,
            $this->repository()->append($id, new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Closed, LeadLifecycleAction::Close), 2),
        );
        self::assertSame(
            LeadLifecyclePersistenceWriteResult::RejectedVersion,
            $this->repository()->append($id, new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), 3),
        );
        self::assertSame(
            LeadLifecyclePersistenceWriteResult::StateConflict,
            $this->repository()->append($id, new LeadLifecycleTransition(LeadLifecycleState::Delivered, LeadLifecycleState::Closed, LeadLifecycleAction::Close), 2),
        );
        self::assertSame(LeadLifecycleState::Created, $this->repository()->read($id)->snapshot?->state);
    }

    protected function leadId(int $number): LeadId
    {
        return LeadId::fromString(sprintf('a4000000-0000-4000-8000-%012d', $number));
    }
}
