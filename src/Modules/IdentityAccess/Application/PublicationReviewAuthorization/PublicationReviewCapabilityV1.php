<?php

namespace Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization;

enum PublicationReviewCapabilityV1: string
{
    case ReadPublicationReviewQueue = 'read_publication_review_queue';
    case ClaimPublicationReview = 'claim_publication_review';
    case BeginPublicationReview = 'begin_publication_review';
    case ApprovePublication = 'approve_publication';
}
