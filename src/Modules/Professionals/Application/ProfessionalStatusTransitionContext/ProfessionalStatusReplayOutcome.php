<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

enum ProfessionalStatusReplayOutcome: string
{
    case AlreadyApplied = 'already_applied';
    case ContextDivergence = 'context_divergence';
    case Conflict = 'conflict';
}
