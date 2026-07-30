<?php

namespace Tests\Unit\PropertyLifecycleWorkflow;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\DeterministicPropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationDiagnosticCode;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationRequest;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceReadResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleStoredState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PropertyLifecycleOrchestratorTest extends TestCase
{
    public function test_allowed_transition_is_forwarded_exactly_once_at_the_next_version(): void
    {
        $store = PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 4, PropertyLifecyclePersistenceWriteResult::Applied);
        $result = $this->orchestrator($store)->transition($this->request(PropertyLifecycleAction::Activate, 4));

        self::assertSame(PropertyLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(PropertyLifecycleState::Draft, $result->transition?->from);
        self::assertSame(PropertyLifecycleState::Active, $result->transition?->to);
        self::assertSame($result->transition, $store->appendedTransition);
        self::assertSame(5, $store->appendedVersion);
        self::assertSame(1, $store->appendCalls);
    }

    public function test_denied_transition_propagates_exact_workflow_diagnostic_without_writing(): void
    {
        $store = PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 1);
        $expected = (new PropertyLifecycleWorkflow)->decide(PropertyLifecycleState::Draft, PropertyLifecycleAction::Decommission);
        $result = $this->orchestrator($store)->transition($this->request(PropertyLifecycleAction::Decommission, 1));

        self::assertSame(PropertyLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertEquals($expected->diagnostic, $result->workflowDiagnostic);
        self::assertSame($expected->diagnostic?->code, $result->workflowDiagnostic?->code);
        self::assertSame(0, $store->appendCalls);
    }

    #[DataProvider('writeResults')]
    public function test_persistence_results_are_mapped_exhaustively(PropertyLifecyclePersistenceWriteResult $write, PropertyLifecycleOrchestrationStatus $status, ?PropertyLifecycleOrchestrationDiagnosticCode $diagnostic): void
    {
        $store = PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 2, $write);
        $result = $this->orchestrator($store)->transition($this->request(PropertyLifecycleAction::Activate, 2));

        self::assertSame($status, $result->status);
        self::assertSame($diagnostic, $result->orchestrationDiagnostic);
    }

    /** @return iterable<string, array{PropertyLifecyclePersistenceWriteResult, PropertyLifecycleOrchestrationStatus, ?PropertyLifecycleOrchestrationDiagnosticCode}> */
    public static function writeResults(): iterable
    {
        yield 'applied' => [PropertyLifecyclePersistenceWriteResult::Applied, PropertyLifecycleOrchestrationStatus::Applied, null];
        yield 'already applied' => [PropertyLifecyclePersistenceWriteResult::AlreadyApplied, PropertyLifecycleOrchestrationStatus::AlreadyApplied, null];
        yield 'version conflict' => [PropertyLifecyclePersistenceWriteResult::RejectedVersion, PropertyLifecycleOrchestrationStatus::ConcurrencyConflict, PropertyLifecycleOrchestrationDiagnosticCode::VersionConflict];
        yield 'state conflict' => [PropertyLifecyclePersistenceWriteResult::StateConflict, PropertyLifecycleOrchestrationStatus::ConcurrencyConflict, PropertyLifecycleOrchestrationDiagnosticCode::StateConflict];
        yield 'transition rejected' => [PropertyLifecyclePersistenceWriteResult::TransitionRejected, PropertyLifecycleOrchestrationStatus::PersistenceFailure, PropertyLifecycleOrchestrationDiagnosticCode::TransitionRejected];
    }

    public function test_stale_expected_version_returns_conflict_without_write(): void
    {
        $store = PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 3);
        $result = $this->orchestrator($store)->transition($this->request(PropertyLifecycleAction::Activate, 2));

        self::assertSame(PropertyLifecycleOrchestrationStatus::ConcurrencyConflict, $result->status);
        self::assertSame(PropertyLifecycleOrchestrationDiagnosticCode::VersionConflict, $result->orchestrationDiagnostic);
        self::assertSame(0, $store->appendCalls);
    }

    #[DataProvider('readFailures')]
    public function test_missing_and_corrupted_reads_are_typed_without_write(PropertyLifecyclePersistenceReadResult $read, PropertyLifecycleOrchestrationDiagnosticCode $diagnostic): void
    {
        $store = new PropertyLifecycleStoreSpy($read);
        $result = $this->orchestrator($store)->transition($this->request(PropertyLifecycleAction::Activate, 1));

        self::assertSame(PropertyLifecycleOrchestrationStatus::PersistenceFailure, $result->status);
        self::assertSame($diagnostic, $result->orchestrationDiagnostic);
        self::assertSame(0, $store->appendCalls);
    }

    /** @return iterable<string, array{PropertyLifecyclePersistenceReadResult, PropertyLifecycleOrchestrationDiagnosticCode}> */
    public static function readFailures(): iterable
    {
        $id = self::propertyId();
        yield 'missing' => [PropertyLifecyclePersistenceReadResult::missing($id), PropertyLifecycleOrchestrationDiagnosticCode::CurrentStateMissing];
        yield 'corrupted' => [PropertyLifecyclePersistenceReadResult::corrupted($id), PropertyLifecycleOrchestrationDiagnosticCode::StoredStateCorrupted];
    }

    public function test_read_and_write_exceptions_are_closed_persistence_failures(): void
    {
        $readFailure = PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 1);
        $readFailure->throwOnRead = true;
        $writeFailure = PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 1);
        $writeFailure->throwOnAppend = true;

        foreach ([$readFailure, $writeFailure] as $store) {
            $result = $this->orchestrator($store)->transition($this->request(PropertyLifecycleAction::Activate, 1));
            self::assertSame(PropertyLifecycleOrchestrationStatus::PersistenceFailure, $result->status);
            self::assertSame(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure, $result->orchestrationDiagnostic);
        }
    }

    public function test_request_rejects_non_positive_expected_version(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PropertyLifecycleOrchestrationRequest(self::propertyId(), PropertyLifecycleAction::Activate, 0);
    }

    public function test_identical_inputs_and_store_outcomes_are_deterministic(): void
    {
        $first = $this->orchestrator(PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 1))->transition($this->request(PropertyLifecycleAction::Activate, 1));
        $second = $this->orchestrator(PropertyLifecycleStoreSpy::found(PropertyLifecycleState::Draft, 1))->transition($this->request(PropertyLifecycleAction::Activate, 1));

        self::assertEquals($first, $second);
    }

    private function orchestrator(PropertyLifecycleWorkflowStore $store): DeterministicPropertyLifecycleOrchestrator
    {
        return new DeterministicPropertyLifecycleOrchestrator(new PropertyLifecycleWorkflow, $store);
    }

    private function request(PropertyLifecycleAction $action, int $version): PropertyLifecycleOrchestrationRequest
    {
        return new PropertyLifecycleOrchestrationRequest(self::propertyId(), $action, $version);
    }

    private static function propertyId(): PropertyId
    {
        return PropertyId::fromString('22222222-2222-4222-8222-222222222222');
    }
}

final class PropertyLifecycleStoreSpy implements PropertyLifecycleWorkflowStore
{
    public int $appendCalls = 0;

    public ?PropertyLifecycleTransition $appendedTransition = null;

    public ?int $appendedVersion = null;

    public bool $throwOnRead = false;

    public bool $throwOnAppend = false;

    public function __construct(private readonly PropertyLifecyclePersistenceReadResult $readResult, private readonly PropertyLifecyclePersistenceWriteResult $writeResult = PropertyLifecyclePersistenceWriteResult::Applied) {}

    public static function found(PropertyLifecycleState $state, int $version, PropertyLifecyclePersistenceWriteResult $writeResult = PropertyLifecyclePersistenceWriteResult::Applied): self
    {
        $id = PropertyId::fromString('22222222-2222-4222-8222-222222222222');

        return new self(PropertyLifecyclePersistenceReadResult::found(new PropertyLifecycleStoredState($id, $state, $version)), $writeResult);
    }

    public function initialize(PropertyId $propertyId, PropertyLifecycleState $state): PropertyLifecyclePersistenceWriteResult
    {
        throw new RuntimeException('Initialization is outside orchestration scope.');
    }

    public function append(PropertyId $propertyId, PropertyLifecycleTransition $transition, int $version): PropertyLifecyclePersistenceWriteResult
    {
        $this->appendCalls++;
        $this->appendedTransition = $transition;
        $this->appendedVersion = $version;
        if ($this->throwOnAppend) {
            throw new RuntimeException('Persistence unavailable.');
        }

        return $this->writeResult;
    }

    public function read(PropertyId $propertyId): PropertyLifecyclePersistenceReadResult
    {
        if ($this->throwOnRead) {
            throw new RuntimeException('Persistence unavailable.');
        }

        return $this->readResult;
    }
}
