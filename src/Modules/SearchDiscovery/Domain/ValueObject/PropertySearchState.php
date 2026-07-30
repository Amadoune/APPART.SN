<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum PropertySearchState: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Archived = 'archived';
}
