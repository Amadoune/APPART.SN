<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

enum ReliabilityOperationsOutboxAppendResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
