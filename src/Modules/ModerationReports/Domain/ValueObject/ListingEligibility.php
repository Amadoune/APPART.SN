<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum ListingEligibility: string
{
    case Missing = 'missing';
    case Eligible = 'eligible';
    case Archived = 'archived';
    case Closed = 'closed';
    case Unavailable = 'unavailable';
    case NotModeratable = 'not_moderatable';

    public function permitsModeration(): bool
    {
        return $this !== self::Missing && $this !== self::NotModeratable;
    }
}
