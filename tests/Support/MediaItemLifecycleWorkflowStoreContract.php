<?php

namespace Tests\Support;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadStatus;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceWriteResult;
use PHPUnit\Framework\TestCase;

abstract class MediaItemLifecycleWorkflowStoreContract extends TestCase
{
    abstract protected function store(): MediaItemLifecycleWorkflowStore;

    abstract protected function resetStore(): void;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetStore();
    }

    public function test_initialization_is_exact_and_idempotent(): void
    {
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::Applied, $this->store()->initialize($this->id()));
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::AlreadyApplied, $this->store()->initialize($this->id()));
        $read = $this->store()->read($this->id());
        self::assertSame(MediaItemLifecyclePersistenceReadStatus::Found, $read->status);
        self::assertSame(MediaItemLifecycleState::Active, $read->snapshot?->state);
        self::assertSame(1, $read->snapshot?->version);
    }

    public function test_each_certified_terminal_transition_is_persisted(): void
    {
        foreach ([[$this->id(), $this->remove(), MediaItemLifecycleState::Removed], [$this->otherId(), $this->archive(), MediaItemLifecycleState::Archived]] as [$id, $transition, $state]) {
            $this->store()->initialize($id);
            self::assertSame(MediaItemLifecyclePersistenceWriteResult::Applied, $this->store()->append($id, $transition, 2));
            self::assertSame($state, $this->store()->read($id)->snapshot?->state);
            self::assertSame(2, $this->store()->read($id)->snapshot?->version);
        }
    }

    public function test_identical_append_is_idempotent(): void
    {
        $this->store()->initialize($this->id());
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::Applied, $this->store()->append($this->id(), $this->remove(), 2));
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::AlreadyApplied, $this->store()->append($this->id(), $this->remove(), 2));
    }

    public function test_version_and_state_conflicts_are_closed_results(): void
    {
        $this->store()->initialize($this->id());
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::RejectedVersion, $this->store()->append($this->id(), $this->remove(), 4));
        $invalidState = new MediaItemLifecycleTransition(MediaItemLifecycleState::Removed, MediaItemLifecycleState::Archived, MediaItemLifecycleAction::Archive);
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::StateConflict, $this->store()->append($this->id(), $invalidState, 2));
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::RejectedVersion, $this->store()->append($this->otherId(), $this->remove(), 2));
    }

    public function test_uncertified_transition_is_rejected(): void
    {
        $this->store()->initialize($this->id());
        $invalid = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Active, MediaItemLifecycleAction::Remove);
        self::assertSame(MediaItemLifecyclePersistenceWriteResult::TransitionRejected, $this->store()->append($this->id(), $invalid, 2));
    }

    public function test_missing_read_is_explicit(): void
    {
        $read = $this->store()->read($this->id());
        self::assertSame(MediaItemLifecyclePersistenceReadStatus::Missing, $read->status);
        self::assertNull($read->snapshot);
    }

    protected function id(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000001');
    }

    protected function otherId(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000002');
    }

    protected function remove(): MediaItemLifecycleTransition
    {
        return new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
    }

    protected function archive(): MediaItemLifecycleTransition
    {
        return new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Archived, MediaItemLifecycleAction::Archive);
    }
}
