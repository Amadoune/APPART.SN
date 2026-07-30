<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum ListingSearchState: string
{
    case Published = 'published';
    case TemporarilyUnavailable = 'temporarily_unavailable';
    case Terminal = 'terminal';
}
