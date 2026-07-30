<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

enum PropertyAvailability: string
{
    case Missing = 'missing';
    case Ineligible = 'ineligible';
    case Archived = 'archived';
    case Unavailable = 'unavailable';
    case Eligible = 'eligible';
}
