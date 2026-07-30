<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

enum PlaceLifecycleDecision: string
{
    case Applied = 'applied';
    case AlreadyInState = 'already_in_state';
    case TerminalState = 'terminal_state';
    case SameIdentity = 'same_identity';
    case TargetDisabled = 'target_disabled';
    case TargetMerged = 'target_merged';
    case DifferentType = 'different_type';
    case DifferentCountry = 'different_country';
    case InvalidContext = 'invalid_context';
}
