<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration;

enum IdentityAccessAtomicOperation: string
{
    case Authentication = 'Authentication';
    case Session = 'Session';
    case PasswordRecovery = 'PasswordRecovery';
    case ContactChange = 'ContactChange';
    case ClaimSwap = 'ClaimSwap';
    case ProfileMutation = 'ProfileMutation';
    case AccountClosure = 'AccountClosure';
    case Reopen = 'Reopen';
}
