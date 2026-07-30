<?php

namespace Tests\Unit\Contracts\PropertyLifecycleWorkflow;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceReadStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflowResult;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class PropertyLifecycleWorkflowStoreContract extends TestCase
{
    abstract protected function repository(): PropertyLifecycleWorkflowStore;

    public function test_missing_initialization_read_and_idempotence_are_explicit(): void
    {
        $id = $this->propertyId(1);
        self::assertSame(PropertyLifecyclePersistenceReadStatus::Missing, $this->repository()->read($id)->status);
        self::assertSame(PropertyLifecyclePersistenceWriteResult::Applied, $this->repository()->initialize($id, PropertyLifecycleState::Draft));
        self::assertSame(PropertyLifecyclePersistenceWriteResult::AlreadyApplied, $this->repository()->initialize($id, PropertyLifecycleState::Draft));
        self::assertSame(PropertyLifecycleState::Draft, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(1, $this->repository()->read($id)->snapshot?->version);
    }

    #[DataProvider('states')]
    public function test_every_state_round_trips(PropertyLifecycleState $state, int $number): void
    {
        $id = $this->propertyId($number);
        self::assertSame(PropertyLifecyclePersistenceWriteResult::Applied, $this->repository()->initialize($id, $state));
        self::assertSame($state, $this->repository()->read($id)->snapshot?->state);
    }

    /** @return iterable<array{PropertyLifecycleState, int}> */
    public static function states(): iterable
    {
        foreach (PropertyLifecycleState::cases() as $index => $state) {
            yield $state->value => [$state, $index + 10];
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_every_certified_transition_is_persisted(PropertyLifecycleState $from, PropertyLifecycleAction $action, int $number): void
    {
        $id = $this->propertyId($number);
        $decision = (new PropertyLifecycleWorkflow)->decide($from, $action);
        self::assertSame(PropertyLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertNotNull($decision->transition);
        $this->repository()->initialize($id, $from);

        self::assertSame(PropertyLifecyclePersistenceWriteResult::Applied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame(PropertyLifecyclePersistenceWriteResult::AlreadyApplied, $this->repository()->append($id, $decision->transition, 2));
        self::assertSame($decision->transition->to, $this->repository()->read($id)->snapshot?->state);
        self::assertSame(2, $this->repository()->read($id)->snapshot?->version);
    }

    /** @return iterable<array{PropertyLifecycleState, PropertyLifecycleAction, int}> */
    public static function allowedTransitions(): iterable
    {
        $number = 100;
        foreach (PropertyLifecycleState::cases() as $state) {
            foreach (PropertyLifecycleAction::cases() as $action) {
                if ((new PropertyLifecycleWorkflow)->decide($state, $action)->result === PropertyLifecycleWorkflowResult::Allowed) {
                    yield $state->value.'>'.$action->value => [$state, $action, $number++];
                }
            }
        }
    }

    public function test_forbidden_transition_version_gap_and_state_conflict_are_rejected(): void
    {
        $id = $this->propertyId(500);
        $this->repository()->initialize($id, PropertyLifecycleState::Draft);
        self::assertSame(
            PropertyLifecyclePersistenceWriteResult::TransitionRejected,
            $this->repository()->append($id, new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Decommissioned, PropertyLifecycleAction::Decommission), 2),
        );
        self::assertSame(
            PropertyLifecyclePersistenceWriteResult::RejectedVersion,
            $this->repository()->append($id, new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate), 3),
        );
        self::assertSame(
            PropertyLifecyclePersistenceWriteResult::StateConflict,
            $this->repository()->append($id, new PropertyLifecycleTransition(PropertyLifecycleState::Active, PropertyLifecycleState::Unavailable, PropertyLifecycleAction::MarkUnavailable), 2),
        );
        self::assertSame(PropertyLifecycleState::Draft, $this->repository()->read($id)->snapshot?->state);
    }

    protected function propertyId(int $number): PropertyId
    {
        return PropertyId::fromString(sprintf('97000000-0000-4000-8000-%012d', $number));
    }
}
