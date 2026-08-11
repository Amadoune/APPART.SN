<?php

namespace Appart\Modules\ListingLifecycle\Application\RevisionAuthority;

enum ListingRevisionOperation: string
{
    case Submit = 'submit';
    case BeginReview = 'begin_review';
    case ApproveAndPublish = 'approve_and_publish';
}
