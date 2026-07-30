<?php

namespace App\Application\PublicProjectionReconciliation;

final readonly class PublicProjectionReconciliationPolicy
{
    public function analyze(PublicProjectionDivergence $divergence): PublicProjectionReconciliationAnalysis
    {
        return match ($divergence->type) {
            PublicProjectionDivergenceType::MissingMessage => new PublicProjectionReconciliationAnalysis($divergence, $divergence->observation->expectedMessageId === null ? PublicProjectionReconciliationDecision::ReplayRange : PublicProjectionReconciliationDecision::ReplayMessage, 'Expected durable message is absent.'),
            PublicProjectionDivergenceType::SequenceGap => new PublicProjectionReconciliationAnalysis($divergence, PublicProjectionReconciliationDecision::ReplayRange, 'Causal sequence contains a gap.'),
            PublicProjectionDivergenceType::HighWatermarkDivergence => new PublicProjectionReconciliationAnalysis($divergence, PublicProjectionReconciliationDecision::ReplayHighWatermark, 'Outbox and projection high-watermarks differ.'),
            PublicProjectionDivergenceType::BlockedBySourceReadiness => new PublicProjectionReconciliationAnalysis($divergence, PublicProjectionReconciliationDecision::WaitForSourceReadiness, 'Stable public source revision is unavailable.'),
            PublicProjectionDivergenceType::BlockedBySequenceGap => new PublicProjectionReconciliationAnalysis($divergence, PublicProjectionReconciliationDecision::ReplayAggregate, 'Durable sequence blockage requires targeted aggregate replay.'),
        };
    }
}
