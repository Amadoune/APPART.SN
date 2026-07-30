<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

enum RenewalRoute: string
{
    case DirectPublication = 'direct_publication';
    case NewReview = 'new_review';
    case Prohibited = 'prohibited';
}
