<?php

namespace App\Application\PublicProjectionUpdater;

enum PublicListingProjectionUpdateOutcome: string
{
    case SourceUnavailable = 'source_unavailable';
    case ProjectionUnavailable = 'projection_unavailable';
    case PromotionNotReady = 'promotion_not_ready';
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedObsolete = 'rejected_obsolete';
    case DivergentWatermark = 'divergent_watermark';
    case IncompleteWatermark = 'incomplete_watermark';
    case CanonicalCollision = 'canonical_collision';
    case CanonicalReplacementRequired = 'canonical_replacement_required';
    case HistoricalReservationConflict = 'historical_reservation_conflict';
    case GenerationMismatch = 'generation_mismatch';
}
