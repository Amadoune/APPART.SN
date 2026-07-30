<?php

namespace App\Application\PublicProjectionReconciliation;

use App\Application\PublicProjectionReconciliation\Contract\PublicProjectionReconciliationSource;
use App\Application\PublicProjectionReconciliation\Contract\PublicProjectionReplayPlanner;
use App\Application\PublicProjectionRetry\PublicProjectionReplayAuthorization;
use App\Application\PublicProjectionRetry\PublicProjectionReplayRequest;
use App\Application\PublicProjectionRetry\PublicProjectionReplayScope;
use InvalidArgumentException;

final readonly class PublicProjectionReconciliationEngine
{
    public function __construct(
        private PublicProjectionReconciliationSource $source,
        private PublicProjectionReconciliationDetector $detector,
        private PublicProjectionReconciliationPolicy $policy,
        private PublicProjectionReplayPlanner $planner,
        private PublicProjectionReplayAuthorization $authorization,
        private int $batchSize,
    ) {
        if ($batchSize < 1) {
            throw new InvalidArgumentException('Reconciliation batch size must be positive.');
        }
    }

    public function runOnce(?string $checkpoint = null): PublicProjectionReconciliationResult
    {
        $page = $this->source->read($checkpoint, $this->batchSize);
        $analyses = [];
        $scheduled = 0;
        foreach ($page->observations as $observation) {
            foreach ($this->detector->detect($observation) as $divergence) {
                $analysis = $this->policy->analyze($divergence);
                $analyses[] = $analysis;
                $request = $this->request($analysis);
                if ($request !== null) {
                    $this->planner->schedule($request);
                    $scheduled++;
                }
            }
        }

        return new PublicProjectionReconciliationResult(count($page->observations), $scheduled, $analyses, $page->nextCheckpoint);
    }

    private function request(PublicProjectionReconciliationAnalysis $analysis): ?PublicProjectionReplayRequest
    {
        $observation = $analysis->divergence->observation;

        return match ($analysis->decision) {
            PublicProjectionReconciliationDecision::ReplayMessage => new PublicProjectionReplayRequest(PublicProjectionReplayScope::Message, $observation->consumerId, $this->authorization, messageId: $observation->expectedMessageId),
            PublicProjectionReconciliationDecision::ReplayRange => new PublicProjectionReplayRequest(PublicProjectionReplayScope::Range, $observation->consumerId, $this->authorization, sourceModule: $observation->sourceModule, aggregateId: $observation->aggregateId, fromExclusive: $observation->progress, toInclusive: $observation->outboxHighWatermark),
            PublicProjectionReconciliationDecision::ReplayHighWatermark => new PublicProjectionReplayRequest(PublicProjectionReplayScope::HighWatermark, $observation->consumerId, $this->authorization, sourceModule: $observation->sourceModule, toInclusive: $observation->outboxHighWatermark),
            PublicProjectionReconciliationDecision::ReplayAggregate => new PublicProjectionReplayRequest(PublicProjectionReplayScope::Aggregate, $observation->consumerId, $this->authorization, sourceModule: $observation->sourceModule, aggregateId: $observation->aggregateId),
            PublicProjectionReconciliationDecision::WaitForSourceReadiness => null,
        };
    }
}
