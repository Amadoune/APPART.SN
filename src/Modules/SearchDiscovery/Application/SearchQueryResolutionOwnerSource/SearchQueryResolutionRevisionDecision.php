<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource;

enum SearchQueryResolutionRevisionDecision: string
{
    case Found = 'found';
    case Empty = 'empty';
}
