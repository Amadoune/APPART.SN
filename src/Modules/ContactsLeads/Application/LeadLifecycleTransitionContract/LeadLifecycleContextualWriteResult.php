<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

enum LeadLifecycleContextualWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
    case ContextDivergence = 'context_divergence';
    case Corrupted = 'corrupted';
}
