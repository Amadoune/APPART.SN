<?php

namespace App\Application\PropertyListingResolution;

enum PropertyListingsPageStatus: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Completed = 'completed';
    case InvalidIdentity = 'invalid_identity';
    case Corrupted = 'corrupted';
}
