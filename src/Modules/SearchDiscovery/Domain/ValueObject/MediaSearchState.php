<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum MediaSearchState: string
{
    case Ready = 'ready';
    case MissingPrimary = 'missing_primary';
    case Unavailable = 'unavailable';
}
