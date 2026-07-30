<?php

namespace App\Application\PublicProjectionReconciliation;

enum PublicProjectionReconciliationDecision: string
{
    case ReplayMessage = 'replay_message';
    case ReplayRange = 'replay_range';
    case ReplayHighWatermark = 'replay_high_watermark';
    case ReplayAggregate = 'replay_aggregate';
    case WaitForSourceReadiness = 'wait_for_source_readiness';
}
