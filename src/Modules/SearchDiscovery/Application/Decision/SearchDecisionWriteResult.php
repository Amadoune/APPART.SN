<?php

namespace Appart\Modules\SearchDiscovery\Application\Decision;

enum SearchDecisionWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedObsolete = 'rejected_obsolete';
    case Divergent = 'divergent';
}
