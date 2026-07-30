<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

enum ListingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case ChangesRequested = 'changes_requested';
    case Published = 'published';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Withdrawn = 'withdrawn';
    case Rejected = 'rejected';
    case Archived = 'archived';
}
