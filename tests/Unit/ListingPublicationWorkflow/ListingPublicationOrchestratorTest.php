<?php

namespace Tests\Unit\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ListingPublicationOrchestratorTest extends TestCase
{
    public function test_allowed_transition_is_persisted_exactly_once_at_the_next_version(): void
    {
        $store = OrchestrationStoreSpy::found(ListingPublicationState::Draft, 4, ListingPublicationPersistenceWriteResult::Applied);
        $result = $this->orchestrator($store)->transition($this->request(ListingPublicationAction::Submit, 4));

        self::assertSame(ListingPublicationOrchestrationStatus::Applied, $result->status);
        self::assertSame(ListingPublicationState::Draft, $result->transition?->from);
        self::assertSame(ListingPublicationState::Submitted, $result->transition?->to);
        self::assertSame($result->transition, $store->appendedTransition);
        self::assertSame(5, $store->appendedVersion);
        self::assertSame(1, $store->appendCalls);
    }

    public function test_denied_transition_propagates_the_exact_workflow_diagnostic_without_writing(): void
    {
        $store = OrchestrationStoreSpy::found(ListingPublicationState::Draft, 1);
        $expected = (new ListingPublicationWorkflow)->decide(ListingPublicationState::Draft, ListingPublicationAction::Expire);
        $result = $this->orchestrator($store)->transition($this->request(ListingPublicationAction::Expire, 1));

        self::assertSame(ListingPublicationOrchestrationStatus::Denied, $result->status);
        self::assertEquals($expected->diagnostic, $result->workflowDiagnostic);
        self::assertSame($expected->diagnostic?->code, $result->workflowDiagnostic?->code);
        self::assertSame(0, $store->appendCalls);
    }

    #[DataProvider('writeResultProvider')]
    public function test_store_results_are_mapped_exhaustively(ListingPublicationPersistenceWriteResult $write, ListingPublicationOrchestrationStatus $status, ?ListingPublicationOrchestrationDiagnosticCode $diagnostic): void
    {
        $store = OrchestrationStoreSpy::found(ListingPublicationState::Draft, 2, $write);
        $result = $this->orchestrator($store)->transition($this->request(ListingPublicationAction::Submit, 2));

        self::assertSame($status, $result->status);
        self::assertSame($diagnostic, $result->orchestrationDiagnostic);
    }

    /** @return iterable<string, array{ListingPublicationPersistenceWriteResult, ListingPublicationOrchestrationStatus, ?ListingPublicationOrchestrationDiagnosticCode}> */
    public static function writeResultProvider(): iterable
    {
        yield 'applied' => [ListingPublicationPersistenceWriteResult::Applied, ListingPublicationOrchestrationStatus::Applied, null];
        yield 'already applied' => [ListingPublicationPersistenceWriteResult::AlreadyApplied, ListingPublicationOrchestrationStatus::AlreadyApplied, null];
        yield 'version conflict' => [ListingPublicationPersistenceWriteResult::RejectedVersion, ListingPublicationOrchestrationStatus::ConcurrencyConflict, ListingPublicationOrchestrationDiagnosticCode::VersionConflict];
        yield 'state conflict' => [ListingPublicationPersistenceWriteResult::StateConflict, ListingPublicationOrchestrationStatus::ConcurrencyConflict, ListingPublicationOrchestrationDiagnosticCode::StateConflict];
        yield 'persistence rejection' => [ListingPublicationPersistenceWriteResult::TransitionRejected, ListingPublicationOrchestrationStatus::PersistenceFailure, ListingPublicationOrchestrationDiagnosticCode::TransitionRejected];
    }

    public function test_stale_expected_version_returns_conflict_before_decision_or_write(): void
    {
        $store = OrchestrationStoreSpy::found(ListingPublicationState::Draft, 3);
        $result = $this->orchestrator($store)->transition($this->request(ListingPublicationAction::Submit, 2));

        self::assertSame(ListingPublicationOrchestrationStatus::ConcurrencyConflict, $result->status);
        self::assertSame(ListingPublicationOrchestrationDiagnosticCode::VersionConflict, $result->orchestrationDiagnostic);
        self::assertSame(0, $store->appendCalls);
    }

    #[DataProvider('readFailureProvider')]
    public function test_unusable_reads_are_typed_without_calling_the_workflow(ListingPublicationPersistenceReadResult $read, ListingPublicationOrchestrationDiagnosticCode $diagnostic): void
    {
        $store = new OrchestrationStoreSpy($read);
        $result = $this->orchestrator($store)->transition($this->request(ListingPublicationAction::Submit, 1));

        self::assertSame(ListingPublicationOrchestrationStatus::PersistenceFailure, $result->status);
        self::assertSame($diagnostic, $result->orchestrationDiagnostic);
        self::assertSame(0, $store->appendCalls);
    }

    /** @return iterable<string, array{ListingPublicationPersistenceReadResult, ListingPublicationOrchestrationDiagnosticCode}> */
    public static function readFailureProvider(): iterable
    {
        $id = ListingId::fromString('11111111-1111-4111-8111-111111111111');
        yield 'missing' => [ListingPublicationPersistenceReadResult::missing($id), ListingPublicationOrchestrationDiagnosticCode::CurrentStateMissing];
        yield 'corrupted' => [ListingPublicationPersistenceReadResult::corrupted($id), ListingPublicationOrchestrationDiagnosticCode::StoredStateCorrupted];
    }

    public function test_infrastructure_exception_is_a_closed_persistence_failure(): void
    {
        $store = OrchestrationStoreSpy::found(ListingPublicationState::Draft, 1);
        $store->throwOnAppend = true;
        $result = $this->orchestrator($store)->transition($this->request(ListingPublicationAction::Submit, 1));

        self::assertSame(ListingPublicationOrchestrationStatus::PersistenceFailure, $result->status);
        self::assertSame(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure, $result->orchestrationDiagnostic);
    }

    public function test_same_input_and_state_produce_the_same_result(): void
    {
        $first = $this->orchestrator(OrchestrationStoreSpy::found(ListingPublicationState::Draft, 1))->transition($this->request(ListingPublicationAction::Submit, 1));
        $second = $this->orchestrator(OrchestrationStoreSpy::found(ListingPublicationState::Draft, 1))->transition($this->request(ListingPublicationAction::Submit, 1));

        self::assertEquals($first, $second);
    }

    private function orchestrator(ListingPublicationWorkflowStore $store): DeterministicListingPublicationOrchestrator
    {
        return new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $store);
    }

    private function request(ListingPublicationAction $action, int $version): ListingPublicationOrchestrationRequest
    {
        return new ListingPublicationOrchestrationRequest(ListingId::fromString('11111111-1111-4111-8111-111111111111'), $action, $version);
    }
}

final class OrchestrationStoreSpy implements ListingPublicationWorkflowStore
{
    public int $appendCalls = 0;

    public ?ListingPublicationTransition $appendedTransition = null;

    public ?int $appendedVersion = null;

    public bool $throwOnAppend = false;

    public function __construct(private readonly ListingPublicationPersistenceReadResult $readResult, private readonly ListingPublicationPersistenceWriteResult $writeResult = ListingPublicationPersistenceWriteResult::Applied) {}

    public static function found(ListingPublicationState $state, int $version, ListingPublicationPersistenceWriteResult $writeResult = ListingPublicationPersistenceWriteResult::Applied): self
    {
        $id = ListingId::fromString('11111111-1111-4111-8111-111111111111');

        return new self(ListingPublicationPersistenceReadResult::found(new ListingPublicationStoredState($id, $state, $version)), $writeResult);
    }

    public function initialize(ListingId $listingId, ListingPublicationState $state): ListingPublicationPersistenceWriteResult
    {
        throw new RuntimeException('Initialization is outside orchestration scope.');
    }

    public function append(ListingId $listingId, ListingPublicationTransition $transition, int $version): ListingPublicationPersistenceWriteResult
    {
        $this->appendCalls++;
        $this->appendedTransition = $transition;
        $this->appendedVersion = $version;
        if ($this->throwOnAppend) {
            throw new RuntimeException('Persistence unavailable.');
        }

        return $this->writeResult;
    }

    public function read(ListingId $listingId): ListingPublicationPersistenceReadResult
    {
        return $this->readResult;
    }
}
