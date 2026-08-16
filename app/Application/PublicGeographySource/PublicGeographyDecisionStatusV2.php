<?php

namespace App\Application\PublicGeographySource;

enum PublicGeographyDecisionStatusV2: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
}
