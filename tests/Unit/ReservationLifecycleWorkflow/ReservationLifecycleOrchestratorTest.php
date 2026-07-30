<?php

namespace Tests\Unit\ReservationLifecycleWorkflow;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleDiagnosticCode;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\DeterministicReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationRequest;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceWriteResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecycleStoredState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReservationLifecycleOrchestratorTest extends TestCase
{
    public function test_reads_decides_then_writes_the_exact_certified_transition_and_next_version(): void
    {
        $store = ReservationOrchestrationStoreSpy::found($this->id(), ReservationLifecycleState::Draft, 1, ReservationLifecyclePersistenceWriteResult::Applied);
        $result = $this->orchestrator($store)->transition($this->request(ReservationLifecycleAction::Submit));

        self::assertSame(ReservationLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(['read', 'append'], $store->calls);
        self::assertEquals(new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit), $store->transition);
        self::assertSame(2, $store->version);
        self::assertSame($this->id()->value, $store->writtenId?->value);
    }

    public function test_missing_reservation_stops_after_read(): void
    {
        $store = new ReservationOrchestrationStoreSpy(ReservationLifecyclePersistenceReadResult::missing($this->id()), ReservationLifecyclePersistenceWriteResult::Applied);

        self::assertSame(ReservationLifecycleOrchestrationStatus::Missing, $this->orchestrator($store)->transition($this->request(ReservationLifecycleAction::Submit))->status);
        self::assertSame(['read'], $store->calls);
    }

    public function test_version_conflict_stops_before_decision_write(): void
    {
        $store = ReservationOrchestrationStoreSpy::found($this->id(), ReservationLifecycleState::Draft, 2, ReservationLifecyclePersistenceWriteResult::Applied);

        self::assertSame(ReservationLifecycleOrchestrationStatus::VersionConflict, $this->orchestrator($store)->transition($this->request(ReservationLifecycleAction::Submit))->status);
        self::assertSame(['read'], $store->calls);
    }

    public function test_workflow_denial_is_propagated_exactly_without_write(): void
    {
        $store = ReservationOrchestrationStoreSpy::found($this->id(), ReservationLifecycleState::Draft, 1, ReservationLifecyclePersistenceWriteResult::Applied);
        $result = $this->orchestrator($store)->transition($this->request(ReservationLifecycleAction::Confirm));

        self::assertSame(ReservationLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertSame(ReservationLifecycleDiagnosticCode::TransitionForbidden, $result->diagnostic?->code);
        self::assertSame(['read'], $store->calls);
    }

    #[DataProvider('writeResults')]
    public function test_store_results_are_mapped_without_second_write(ReservationLifecyclePersistenceWriteResult $write, ReservationLifecycleOrchestrationStatus $expected): void
    {
        $store = ReservationOrchestrationStoreSpy::found($this->id(), ReservationLifecycleState::Draft, 1, $write);
        $result = $this->orchestrator($store)->transition($this->request(ReservationLifecycleAction::Submit));

        self::assertSame($expected, $result->status);
        self::assertSame(['read', 'append'], $store->calls);
    }

    /** @return iterable<string,array{ReservationLifecyclePersistenceWriteResult,ReservationLifecycleOrchestrationStatus}> */
    public static function writeResults(): iterable
    {
        yield 'already applied' => [ReservationLifecyclePersistenceWriteResult::AlreadyApplied, ReservationLifecycleOrchestrationStatus::AlreadyApplied];
        yield 'version conflict' => [ReservationLifecyclePersistenceWriteResult::RejectedVersion, ReservationLifecycleOrchestrationStatus::VersionConflict];
        yield 'state conflict' => [ReservationLifecyclePersistenceWriteResult::StateConflict, ReservationLifecycleOrchestrationStatus::StateConflict];
        yield 'persistence rejection' => [ReservationLifecyclePersistenceWriteResult::TransitionRejected, ReservationLifecycleOrchestrationStatus::PersistenceCorrupted];
    }

    public function test_corrupted_read_and_technical_exceptions_are_not_exposed(): void
    {
        $corrupted = new ReservationOrchestrationStoreSpy(ReservationLifecyclePersistenceReadResult::corrupted($this->id()), ReservationLifecyclePersistenceWriteResult::Applied);
        self::assertSame(ReservationLifecycleOrchestrationStatus::PersistenceCorrupted, $this->orchestrator($corrupted)->transition($this->request(ReservationLifecycleAction::Submit))->status);
        self::assertSame(['read'], $corrupted->calls);

        $throwingRead = ReservationOrchestrationStoreSpy::throwingOnRead();
        self::assertSame(ReservationLifecycleOrchestrationStatus::PersistenceCorrupted, $this->orchestrator($throwingRead)->transition($this->request(ReservationLifecycleAction::Submit))->status);

        $throwingWrite = ReservationOrchestrationStoreSpy::found($this->id(), ReservationLifecycleState::Draft, 1, ReservationLifecyclePersistenceWriteResult::Applied, true);
        self::assertSame(ReservationLifecycleOrchestrationStatus::PersistenceCorrupted, $this->orchestrator($throwingWrite)->transition($this->request(ReservationLifecycleAction::Submit))->status);
    }

    public function test_status_set_is_closed_and_request_requires_a_positive_version(): void
    {
        self::assertSame(['applied', 'missing', 'version_conflict', 'state_conflict', 'already_applied', 'denied', 'persistence_corrupted'], array_column(ReservationLifecycleOrchestrationStatus::cases(), 'value'));

        $this->expectException(\InvalidArgumentException::class);
        new ReservationLifecycleOrchestrationRequest($this->id(), ReservationLifecycleAction::Submit, 0);
    }

    private function orchestrator(ReservationLifecycleWorkflowStore $store): DeterministicReservationLifecycleEventOrchestrator
    {
        return new DeterministicReservationLifecycleEventOrchestrator(new ReservationLifecycleWorkflow, $store);
    }

    private function request(ReservationLifecycleAction $action): ReservationLifecycleOrchestrationRequest
    {
        return new ReservationLifecycleOrchestrationRequest($this->id(), $action, 1);
    }

    private function id(): ReservationId
    {
        return ReservationId::fromString('98200000-0000-4000-8000-000000000001');
    }
}

final class ReservationOrchestrationStoreSpy implements ReservationLifecycleWorkflowStore
{
    /** @var list<string> */
    public array $calls = [];

    public ?ReservationLifecycleTransition $transition = null;

    public ?int $version = null;

    public ?ReservationId $writtenId = null;

    private bool $throwRead = false;

    public function __construct(
        private readonly ReservationLifecyclePersistenceReadResult $readResult,
        private readonly ReservationLifecyclePersistenceWriteResult $writeResult,
        private readonly bool $throwWrite = false,
    ) {}

    public static function found(ReservationId $id, ReservationLifecycleState $state, int $version, ReservationLifecyclePersistenceWriteResult $write, bool $throwWrite = false): self
    {
        return new self(ReservationLifecyclePersistenceReadResult::found(new ReservationLifecycleStoredState($id, $state, $version)), $write, $throwWrite);
    }

    public static function throwingOnRead(): self
    {
        $id = ReservationId::fromString('98200000-0000-4000-8000-000000000001');
        $spy = new self(ReservationLifecyclePersistenceReadResult::missing($id), ReservationLifecyclePersistenceWriteResult::Applied);
        $spy->throwRead = true;

        return $spy;
    }

    public function initialize(ReservationId $reservationId, ReservationLifecycleState $state): ReservationLifecyclePersistenceWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function append(ReservationId $reservationId, ReservationLifecycleTransition $transition, int $version): ReservationLifecyclePersistenceWriteResult
    {
        $this->calls[] = 'append';
        if ($this->throwWrite) {
            throw new RuntimeException('Persistence detail must not escape.');
        }
        $this->writtenId = $reservationId;
        $this->transition = $transition;
        $this->version = $version;

        return $this->writeResult;
    }

    public function read(ReservationId $reservationId): ReservationLifecyclePersistenceReadResult
    {
        $this->calls[] = 'read';
        if ($this->throwRead) {
            throw new RuntimeException('Persistence detail must not escape.');
        }

        return $this->readResult;
    }
}
