<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

enum MediaItemLifecycleReplayOutcome: string
{
    case AlreadyApplied = 'already_applied';
    case ContextDivergence = 'context_divergence';
    case Conflict = 'conflict';
}
