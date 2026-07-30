<?php

namespace Tests\Support;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceWriteResult;
use PHPUnit\Framework\TestCase;

abstract class ProfessionalStatusWorkflowStoreContract extends TestCase
{
    abstract protected function store(): ProfessionalStatusWorkflowStore;

    abstract protected function resetStore(): void;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetStore();
    }

    public function test_initialization_is_exact_and_idempotent(): void
    {
        self::assertSame(ProfessionalStatusPersistenceWriteResult::Applied, $this->store()->initialize($this->id()));
        self::assertSame(ProfessionalStatusPersistenceWriteResult::AlreadyApplied, $this->store()->initialize($this->id()));
        $read = $this->store()->read($this->id());
        self::assertSame(ProfessionalStatusPersistenceReadStatus::Found, $read->status);
        self::assertSame(ProfessionalStatusState::Active, $read->snapshot?->state);
        self::assertSame(1, $read->snapshot?->version);
    }

    public function test_both_certified_transitions_are_persisted(): void
    {
        $this->store()->initialize($this->id());
        self::assertSame(ProfessionalStatusPersistenceWriteResult::Applied, $this->store()->append($this->id(), $this->suspend(), 2));
        self::assertSame(ProfessionalStatusPersistenceWriteResult::Applied, $this->store()->append($this->id(), $this->reactivate(), 3));
        self::assertSame(ProfessionalStatusState::Active, $this->store()->read($this->id())->snapshot?->state);
        self::assertSame(3, $this->store()->read($this->id())->snapshot?->version);
    }

    public function test_identical_append_is_idempotent(): void
    {
        $this->store()->initialize($this->id());
        self::assertSame(ProfessionalStatusPersistenceWriteResult::Applied, $this->store()->append($this->id(), $this->suspend(), 2));
        self::assertSame(ProfessionalStatusPersistenceWriteResult::AlreadyApplied, $this->store()->append($this->id(), $this->suspend(), 2));
    }

    public function test_version_and_state_conflicts_are_closed_results(): void
    {
        $this->store()->initialize($this->id());
        self::assertSame(ProfessionalStatusPersistenceWriteResult::RejectedVersion, $this->store()->append($this->id(), $this->suspend(), 4));
        self::assertSame(ProfessionalStatusPersistenceWriteResult::StateConflict, $this->store()->append($this->id(), $this->reactivate(), 2));
        self::assertSame(ProfessionalStatusPersistenceWriteResult::RejectedVersion, $this->store()->append($this->otherId(), $this->suspend(), 2));
    }

    public function test_uncertified_transition_is_rejected(): void
    {
        $this->store()->initialize($this->id());
        $invalid = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Active, ProfessionalStatusAction::Suspend);
        self::assertSame(ProfessionalStatusPersistenceWriteResult::TransitionRejected, $this->store()->append($this->id(), $invalid, 2));
    }

    public function test_missing_read_is_explicit(): void
    {
        $read = $this->store()->read($this->id());
        self::assertSame(ProfessionalStatusPersistenceReadStatus::Missing, $read->status);
        self::assertNull($read->snapshot);
    }

    protected function id(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000001');
    }

    protected function otherId(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000002');
    }

    protected function suspend(): ProfessionalStatusTransition
    {
        return new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
    }

    protected function reactivate(): ProfessionalStatusTransition
    {
        return new ProfessionalStatusTransition(ProfessionalStatusState::Suspended, ProfessionalStatusState::Active, ProfessionalStatusAction::Reactivate);
    }
}
