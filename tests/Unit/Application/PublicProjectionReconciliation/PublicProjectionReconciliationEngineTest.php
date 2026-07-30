<?php

namespace Tests\Unit\Application\PublicProjectionReconciliation;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionReconciliation\PublicProjectionDivergenceType;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationDecision;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationDetector;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationEngine;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationObservation;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationPolicy;
use App\Application\PublicProjectionRetry\PublicProjectionReplayAuthorization;
use App\Application\PublicProjectionRetry\PublicProjectionReplayScope;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\PublicProjectionReconciliation\Support\FakePublicProjectionReconciliationSource;
use Tests\Unit\Application\PublicProjectionReconciliation\Support\FakePublicProjectionReplayPlanner;

final class PublicProjectionReconciliationEngineTest extends TestCase
{
    public function test_detector_explains_every_divergence_without_silent_repair(): void
    {
        $observation = $this->observation(messageMissing: true, sequenceGap: true, projection: $this->order(1), blockedReadiness: true, blockedSequence: true);
        $findings = (new PublicProjectionReconciliationDetector)->detect($observation);

        self::assertSame(PublicProjectionDivergenceType::cases(), array_map(static fn ($finding): PublicProjectionDivergenceType => $finding->type, $findings));
    }

    public function test_policy_maps_every_divergence_to_an_explicit_decision(): void
    {
        $detector = new PublicProjectionReconciliationDetector;
        $policy = new PublicProjectionReconciliationPolicy;
        $findings = $detector->detect($this->observation(messageMissing: true, sequenceGap: true, projection: $this->order(1), blockedReadiness: true, blockedSequence: true));

        self::assertSame([
            PublicProjectionReconciliationDecision::ReplayMessage,
            PublicProjectionReconciliationDecision::ReplayRange,
            PublicProjectionReconciliationDecision::ReplayHighWatermark,
            PublicProjectionReconciliationDecision::WaitForSourceReadiness,
            PublicProjectionReconciliationDecision::ReplayAggregate,
        ], array_map(static fn ($finding): PublicProjectionReconciliationDecision => $policy->analyze($finding)->decision, $findings));
        foreach ($findings as $finding) {
            self::assertNotSame('', $policy->analyze($finding)->explanation);
        }
    }

    public function test_engine_plans_only_targeted_replays_and_never_executes_them(): void
    {
        $planner = new FakePublicProjectionReplayPlanner;
        $engine = $this->engine([$this->observation(messageMissing: true, sequenceGap: true, projection: $this->order(1), blockedReadiness: true, blockedSequence: true)], $planner, 10);

        $result = $engine->runOnce();

        self::assertSame(1, $result->observed);
        self::assertSame(4, $result->scheduled);
        self::assertCount(5, $result->analyses);
        self::assertSame([
            PublicProjectionReplayScope::Message,
            PublicProjectionReplayScope::Range,
            PublicProjectionReplayScope::HighWatermark,
            PublicProjectionReplayScope::Aggregate,
        ], array_map(static fn ($request): PublicProjectionReplayScope => $request->scope, $planner->requests));
    }

    public function test_bounded_checkpoint_resumes_without_loss_or_duplicate_planning(): void
    {
        $observations = [$this->observation(messageMissing: true), $this->observation(sequenceGap: true), $this->observation(blockedReadiness: true)];
        $planner = new FakePublicProjectionReplayPlanner;
        $engine = $this->engine($observations, $planner, 2);

        $first = $engine->runOnce();
        $second = $engine->runOnce($first->nextCheckpoint);

        self::assertSame(2, $first->observed);
        self::assertSame('2', $first->nextCheckpoint);
        self::assertSame(1, $second->observed);
        self::assertNull($second->nextCheckpoint);
        self::assertCount(2, $planner->requests);
    }

    /** @param list<PublicProjectionReconciliationObservation> $observations */
    private function engine(array $observations, FakePublicProjectionReplayPlanner $planner, int $batch): PublicProjectionReconciliationEngine
    {
        return new PublicProjectionReconciliationEngine(new FakePublicProjectionReconciliationSource($observations), new PublicProjectionReconciliationDetector, new PublicProjectionReconciliationPolicy, $planner, PublicProjectionReplayAuthorization::fromReference('reconciliation:test'), $batch);
    }

    private function observation(bool $messageMissing = false, bool $sequenceGap = false, ?PublicProjectionDeliveryOrder $projection = null, bool $blockedReadiness = false, bool $blockedSequence = false): PublicProjectionReconciliationObservation
    {
        return new PublicProjectionReconciliationObservation(
            PublicProjectionOutboxConsumerId::fromString('public-projection'),
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateId::fromString('listing:1'),
            $this->order(1),
            $this->order(3),
            $projection ?? $this->order(3),
            PublicProjectionDeliveryMessageId::fromString('ppd-message:expected'),
            $messageMissing,
            $sequenceGap,
            $blockedReadiness,
            $blockedSequence,
        );
    }

    private function order(int $version): PublicProjectionDeliveryOrder
    {
        return new PublicProjectionDeliveryOrder($version, PublicProjectionDeliveryEventIndex::fromInt(1));
    }
}
