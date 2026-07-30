<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

enum AccountStatusDecision: string
{
    case Applied = 'applied';
    case AlreadyInState = 'already_in_state';
    case InvalidContext = 'invalid_context';
}
