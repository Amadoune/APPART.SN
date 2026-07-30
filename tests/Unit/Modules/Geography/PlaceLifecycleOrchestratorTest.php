<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleCurrentState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleDecision;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationRequest;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationStatus;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrator;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceReadResult;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceWriteResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeContextInspector;
use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeReplayClassifier;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspection;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeReplayOutcome;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleOrchestratorTest extends TestCase
{
    public function test_missing_replay_history_continues_through_workflow_and_persistence_in_order(): void
    {
        $calls = [];
        $context = self::context();
        $inspector = new ControlledPlaceMergeInspector(
            PlaceMergeContextInspectionResult::missing($context->sourceId, $context->intentId),
            $calls,
        );
        $classifier = new ControlledPlaceMergeClassifier(PlaceMergeReplayOutcome::Conflict, $calls);
        $store = new ControlledPlaceLifecycleStore(PlaceLifecyclePersistenceWriteResult::Applied, $calls);

        $result = $this->orchestrator($inspector, $classifier, $store)->execute(self::request($context));

        self::assertSame(PlaceLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(PlaceLifecycleState::Merged, $result->state);
        self::assertSame(['inspection', 'persistence'], $calls);
        self::assertSame(0, $classifier->calls);
    }

    #[DataProvider('closedClassificationResults')]
    public function test_closed_classifier_results_stop_before_workflow_persistence(
        PlaceMergeReplayOutcome $outcome,
        PlaceLifecycleOrchestrationStatus $expected,
    ): void {
        $calls = [];
        $context = self::context();
        $inspector = new ControlledPlaceMergeInspector(self::found($context), $calls);
        $classifier = new ControlledPlaceMergeClassifier($outcome, $calls);
        $store = new ControlledPlaceLifecycleStore(PlaceLifecyclePersistenceWriteResult::Applied, $calls);

        $result = $this->orchestrator($inspector, $classifier, $store)->execute(self::request($context));

        self::assertSame($expected, $result->status);
        self::assertSame(PlaceLifecycleState::Enabled, $result->state);
        self::assertSame(['inspection', 'classifier'], $calls);
        self::assertSame(0, $store->appendCalls);
    }

    /** @return iterable<string, array{PlaceMergeReplayOutcome, PlaceLifecycleOrchestrationStatus}> */
    public static function closedClassificationResults(): iterable
    {
        yield 'context divergence' => [PlaceMergeReplayOutcome::ContextDivergence, PlaceLifecycleOrchestrationStatus::ContextDivergence];
        yield 'conflict' => [PlaceMergeReplayOutcome::Conflict, PlaceLifecycleOrchestrationStatus::ReplayConflict];
        yield 'inspection missing' => [PlaceMergeReplayOutcome::InspectionMissing, PlaceLifecycleOrchestrationStatus::InspectionMissing];
        yield 'inspection corrupted' => [PlaceMergeReplayOutcome::InspectionCorrupted, PlaceLifecycleOrchestrationStatus::InspectionCorrupted];
    }

    public function test_replay_candidate_is_confirmed_only_by_persistence(): void
    {
        $calls = [];
        $context = self::context();
        $store = new ControlledPlaceLifecycleStore(PlaceLifecyclePersistenceWriteResult::AlreadyApplied, $calls);

        $result = $this->orchestrator(
            new ControlledPlaceMergeInspector(self::found($context), $calls),
            new ControlledPlaceMergeClassifier(PlaceMergeReplayOutcome::AlreadyApplied, $calls),
            $store,
        )->execute(self::request($context));

        self::assertSame(PlaceLifecycleOrchestrationStatus::AlreadyApplied, $result->status);
        self::assertSame(PlaceLifecycleState::Merged, $result->state);
        self::assertSame(['inspection', 'classifier', 'persistence'], $calls);
        self::assertSame(1, $store->appendCalls);
    }

    public function test_replay_candidate_with_a_different_persistent_identity_is_a_replay_conflict(): void
    {
        $calls = [];
        $context = self::context();
        $result = $this->orchestrator(
            new ControlledPlaceMergeInspector(self::found($context), $calls),
            new ControlledPlaceMergeClassifier(PlaceMergeReplayOutcome::AlreadyApplied, $calls),
            new ControlledPlaceLifecycleStore(PlaceLifecyclePersistenceWriteResult::SourceVersionConflict, $calls),
        )->execute(self::request($context));

        self::assertSame(PlaceLifecycleOrchestrationStatus::ReplayConflict, $result->status);
        self::assertSame(['inspection', 'classifier', 'persistence'], $calls);
    }

    public function test_corrupted_inspection_stops_all_downstream_layers(): void
    {
        $calls = [];
        $context = self::context();
        $classifier = new ControlledPlaceMergeClassifier(PlaceMergeReplayOutcome::Conflict, $calls);
        $store = new ControlledPlaceLifecycleStore(PlaceLifecyclePersistenceWriteResult::Applied, $calls);

        $result = $this->orchestrator(
            new ControlledPlaceMergeInspector(
                PlaceMergeContextInspectionResult::corrupted($context->sourceId, $context->intentId),
                $calls,
            ),
            $classifier,
            $store,
        )->execute(self::request($context));

        self::assertSame(PlaceLifecycleOrchestrationStatus::InspectionCorrupted, $result->status);
        self::assertSame(['inspection'], $calls);
        self::assertSame(0, $classifier->calls);
        self::assertSame(0, $store->appendCalls);
    }

    public function test_workflow_refusal_is_preserved_and_never_persisted(): void
    {
        $calls = [];
        $context = self::context(targetState: PlaceMergeObservedState::Disabled);
        $store = new ControlledPlaceLifecycleStore(PlaceLifecyclePersistenceWriteResult::Applied, $calls);

        $result = $this->orchestrator(
            new ControlledPlaceMergeInspector(
                PlaceMergeContextInspectionResult::missing($context->sourceId, $context->intentId),
                $calls,
            ),
            new ControlledPlaceMergeClassifier(PlaceMergeReplayOutcome::Conflict, $calls),
            $store,
        )->execute(self::request($context));

        self::assertSame(PlaceLifecycleOrchestrationStatus::WorkflowRefused, $result->status);
        self::assertSame(PlaceLifecycleDecision::TargetDisabled, $result->workflowDecision);
        self::assertSame(PlaceLifecycleState::Enabled, $result->state);
        self::assertSame(0, $store->appendCalls);
    }

    #[DataProvider('persistenceResults')]
    public function test_every_persistence_result_has_a_closed_orchestration_result(
        PlaceLifecyclePersistenceWriteResult $write,
        PlaceLifecycleOrchestrationStatus $expected,
    ): void {
        $calls = [];
        $context = self::context();
        $result = $this->orchestrator(
            new ControlledPlaceMergeInspector(
                PlaceMergeContextInspectionResult::missing($context->sourceId, $context->intentId),
                $calls,
            ),
            new ControlledPlaceMergeClassifier(PlaceMergeReplayOutcome::Conflict, $calls),
            new ControlledPlaceLifecycleStore($write, $calls),
        )->execute(self::request($context));

        self::assertSame($expected, $result->status);
    }

    /** @return iterable<string, array{PlaceLifecyclePersistenceWriteResult, PlaceLifecycleOrchestrationStatus}> */
    public static function persistenceResults(): iterable
    {
        yield 'applied' => [PlaceLifecyclePersistenceWriteResult::Applied, PlaceLifecycleOrchestrationStatus::Applied];
        yield 'already applied' => [PlaceLifecyclePersistenceWriteResult::AlreadyApplied, PlaceLifecycleOrchestrationStatus::AlreadyApplied];
        yield 'source conflict' => [PlaceLifecyclePersistenceWriteResult::SourceVersionConflict, PlaceLifecycleOrchestrationStatus::SourceVersionConflict];
        yield 'target conflict' => [PlaceLifecyclePersistenceWriteResult::TargetVersionConflict, PlaceLifecycleOrchestrationStatus::TargetVersionConflict];
        yield 'state conflict' => [PlaceLifecyclePersistenceWriteResult::StateConflict, PlaceLifecycleOrchestrationStatus::StateConflict];
        yield 'transition rejected' => [PlaceLifecyclePersistenceWriteResult::TransitionRejected, PlaceLifecycleOrchestrationStatus::TransitionRejected];
    }

    private function orchestrator(
        PlaceMergeContextInspector $inspector,
        PlaceMergeReplayClassifier $classifier,
        PlaceLifecycleWorkflowStore $store,
    ): PlaceLifecycleOrchestrator {
        return new PlaceLifecycleOrchestrator($inspector, $classifier, new PlaceLifecycleWorkflow, $store);
    }

    private static function request(PlaceMergeContextV1 $context): PlaceLifecycleOrchestrationRequest
    {
        return new PlaceLifecycleOrchestrationRequest(
            new PlaceLifecycleCurrentState($context->sourceId, PlaceLifecycleState::Enabled),
            PlaceLifecycleAction::Merge,
            $context,
        );
    }

    private static function found(PlaceMergeContextV1 $context): PlaceMergeContextInspectionResult
    {
        return PlaceMergeContextInspectionResult::found(
            new PlaceMergeContextInspection($context, $context->expectedSourceVersion->value + 1),
        );
    }

    private static function context(
        PlaceMergeObservedState $targetState = PlaceMergeObservedState::Enabled,
    ): PlaceMergeContextV1 {
        return new PlaceMergeContextV1(
            sourceId: PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            targetId: PlaceId::fromString('10000000-0000-4000-8000-000000000002'),
            expectedSourceVersion: new PlaceMergeExpectedSourceVersion(7),
            observedTargetVersion: new PlaceMergeObservedTargetVersion(11),
            observedTargetState: $targetState,
            observedSourceType: PlaceType::City,
            observedTargetType: PlaceType::City,
            observedSourceCountry: CountryCode::fromString('SN'),
            observedTargetCountry: CountryCode::fromString('SN'),
            actor: PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
            occurredAt: PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
            intentId: PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
        );
    }
}

final class ControlledPlaceMergeInspector implements PlaceMergeContextInspector
{
    /** @param list<string> $calls */
    public function __construct(
        private PlaceMergeContextInspectionResult $result,
        public array &$calls,
    ) {}

    public function inspect(PlaceId $sourceId, PlaceMergeIntentId $intentId): PlaceMergeContextInspectionResult
    {
        $this->calls[] = 'inspection';

        return $this->result;
    }
}

final class ControlledPlaceMergeClassifier implements PlaceMergeReplayClassifier
{
    public int $calls = 0;

    /** @param list<string> $sequence */
    public function __construct(
        private PlaceMergeReplayOutcome $outcome,
        public array &$sequence,
    ) {}

    public function classify(
        PlaceMergeContextV1 $requested,
        PlaceMergeContextInspectionResult $inspected,
    ): PlaceMergeReplayOutcome {
        $this->calls++;
        $this->sequence[] = 'classifier';

        return $this->outcome;
    }
}

final class ControlledPlaceLifecycleStore implements PlaceLifecycleWorkflowStore
{
    public int $appendCalls = 0;

    /** @param list<string> $calls */
    public function __construct(
        private PlaceLifecyclePersistenceWriteResult $write,
        public array &$calls,
    ) {}

    public function initialize(
        PlaceId $placeId,
        PlaceLifecycleState $state,
        int $version,
    ): PlaceLifecyclePersistenceWriteResult {
        return $this->write;
    }

    public function append(
        PlaceLifecycleTransition $transition,
        PlaceMergeContextV1 $context,
    ): PlaceLifecyclePersistenceWriteResult {
        $this->appendCalls++;
        $this->calls[] = 'persistence';

        return $this->write;
    }

    public function read(PlaceId $placeId): PlaceLifecyclePersistenceReadResult
    {
        return PlaceLifecyclePersistenceReadResult::missing($placeId);
    }
}
