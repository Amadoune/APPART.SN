<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

enum AdministrativeActionReplayOutcome: string
{
    case AlreadyApplied = 'already_applied';
    case VersionConflict = 'version_conflict';
    case ContextDivergence = 'context_divergence';
    case TransitionDivergence = 'transition_divergence';
}
