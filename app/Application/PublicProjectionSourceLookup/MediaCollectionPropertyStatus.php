<?php

namespace App\Application\PublicProjectionSourceLookup;

enum MediaCollectionPropertyStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Ambiguous = 'ambiguous';
    case Corrupted = 'corrupted';
}
