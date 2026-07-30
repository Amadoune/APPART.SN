<?php

namespace App\Application\PublicProjectionReconciliation;

enum PublicProjectionDivergenceType: string
{
    case MissingMessage = 'missing_message';
    case SequenceGap = 'sequence_gap';
    case HighWatermarkDivergence = 'high_watermark_divergence';
    case BlockedBySourceReadiness = 'blocked_by_source_readiness';
    case BlockedBySequenceGap = 'blocked_by_sequence_gap';
}
