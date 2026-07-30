<?php

namespace App\Application\PublicProjectionUpdaterIntegration;

enum PublicProjectionSourceResolutionStatus: string
{
    case Resolved = 'resolved';
    case MultiTargetResolved = 'multi_target_resolved';
    case SourceUnavailable = 'source_unavailable';
    case PromotionNotReady = 'promotion_not_ready';
    case WatermarkIncomplete = 'watermark_incomplete';
    case MissingPublicGeographyRevision = 'missing_public_geography_revision';
    case MissingPublicMediaRevision = 'missing_public_media_revision';
    case MissingPublicGeographyAndMediaRevisions = 'missing_public_geography_and_media_revisions';
    case InvalidIdentity = 'invalid_identity';
    case Corrupted = 'corrupted';
    case MediaOwnershipMissing = 'media_ownership_missing';
    case MediaOwnershipAmbiguous = 'media_ownership_ambiguous';
}
