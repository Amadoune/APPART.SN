<?php

namespace App\Application\PublicProjectionStore;

enum PublicListingProjectionState: string
{
    case Current = 'current';
    case Historical = 'historical';
    case Tombstone = 'tombstone';
}
