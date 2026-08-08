<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

enum ExperienceAcceptanceOutboxAppendResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
