<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSource;

enum SearchOwnerRevisionDecision: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';
    case Removed = 'removed';
}
