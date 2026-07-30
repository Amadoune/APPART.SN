<?php

namespace App\Application\PublicGeographyRevision;

enum PublicGeographyRevisionPromotion: string
{
    case Initial = 'initial';
    case Advance = 'advance';
    case AlreadyStable = 'already_stable';
    case Obsolete = 'obsolete';
    case Divergent = 'divergent';
}
