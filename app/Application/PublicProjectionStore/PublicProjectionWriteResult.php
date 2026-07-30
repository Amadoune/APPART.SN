<?php

namespace App\Application\PublicProjectionStore;

enum PublicProjectionWriteResult: string
{
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
