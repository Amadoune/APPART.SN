<?php

namespace Appart\Modules\SearchDiscovery\Application\Decision;

enum SearchDecisionReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
