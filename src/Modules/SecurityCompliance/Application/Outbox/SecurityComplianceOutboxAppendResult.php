<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

enum SecurityComplianceOutboxAppendResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
