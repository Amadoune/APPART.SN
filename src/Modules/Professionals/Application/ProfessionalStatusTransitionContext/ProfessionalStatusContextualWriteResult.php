<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

enum ProfessionalStatusContextualWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case ContextDivergence = 'context_divergence';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
    case Corrupted = 'corrupted';
}
