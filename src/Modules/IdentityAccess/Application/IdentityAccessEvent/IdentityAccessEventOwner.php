<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessEvent;

enum IdentityAccessEventOwner: string
{
    case Profile = 'IdentityAccess.Profile';
    case Closure = 'IdentityAccess.Closure';

    public static function for(IdentityAccessEventType $type): self
    {
        return match ($type) {
            IdentityAccessEventType::ProfileNameChanged,
            IdentityAccessEventType::ProfileEmailChanged,
            IdentityAccessEventType::ProfilePhoneChanged => self::Profile,
            IdentityAccessEventType::ClosureRequested,
            IdentityAccessEventType::AccountClosed,
            IdentityAccessEventType::AccountReopened => self::Closure,
        };
    }
}
