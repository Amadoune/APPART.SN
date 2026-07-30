<?php

namespace App\Application\ModerationOperationalAudit;

enum ModerationOperationalAuditStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RetryScheduled = 'retry_scheduled';
    case Quarantined = 'quarantined';
    case DependencyUnavailable = 'dependency_unavailable';
}
