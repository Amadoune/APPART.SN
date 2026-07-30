<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessEvent;

enum IdentityAccessEventType: string
{
    case ProfileNameChanged = 'identity.profile.name_changed';
    case ProfileEmailChanged = 'identity.profile.email_changed';
    case ProfilePhoneChanged = 'identity.profile.phone_changed';
    case ClosureRequested = 'identity.account.closure_requested';
    case AccountClosed = 'identity.account.closed';
    case AccountReopened = 'identity.account.reopened';
}
