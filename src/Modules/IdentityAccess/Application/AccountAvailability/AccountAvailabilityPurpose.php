<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

enum AccountAvailabilityPurpose: string
{
    case Authenticate = 'Authenticate';
    case RenewSession = 'RenewSession';
    case RecoverPassword = 'RecoverPassword';
    case MutateProfile = 'MutateProfile';
    case RequestClosure = 'RequestClosure';
    case ReopenClosure = 'ReopenClosure';
}
