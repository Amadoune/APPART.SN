<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

enum AdministrativeActionContextualWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case ContextDivergence = 'context_divergence';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionDivergence = 'transition_divergence';
    case EnrollmentDivergence = 'enrollment_divergence';
    case Corrupted = 'corrupted';
    case PersistenceCorrupted = 'persistence_corrupted';
}
