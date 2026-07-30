<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

enum PublicationMediaAvailability: string
{
    case Missing = 'missing';
    case PropertyMismatch = 'property_mismatch';
    case WithoutActiveMedia = 'without_active_media';
    case WithoutPrimaryMedia = 'without_primary_media';
    case Eligible = 'eligible';
}
