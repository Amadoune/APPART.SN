<?php

namespace App\Application\ProjectionRebuildRuntimeSource;

enum CandidateBuildStatus: string
{
    case Built = 'built';
    case SourceBlocked = 'source_blocked';
    case PromotionNotReady = 'promotion_not_ready';
    case CertifiedTransformationRejected = 'certified_transformation_rejected';
    case ProjectionUnavailable = 'projection_unavailable';
}
