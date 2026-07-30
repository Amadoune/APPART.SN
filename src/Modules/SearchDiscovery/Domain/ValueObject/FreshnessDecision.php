<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum FreshnessDecision: string
{
    case Newer = 'newer';
    case Duplicate = 'duplicate';
    case Stale = 'stale';
    case Inconsistent = 'inconsistent';
}
