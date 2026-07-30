<?php

namespace App\Application\PublicMediaRevision;

enum PublicMediaRevisionPromotion: string
{
    case Initial = 'initial';
    case Advance = 'advance';
    case AlreadyStable = 'already_stable';
    case Obsolete = 'obsolete';
    case Divergent = 'divergent';
}
