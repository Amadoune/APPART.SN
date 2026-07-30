<?php

namespace App\Application\PublicGeographySource;

enum PublicGeographyReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
