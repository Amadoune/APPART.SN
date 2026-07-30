<?php

namespace Appart\Modules\IdentityAccess\Application\ModeratorAuthorization;

enum ModerationCapabilityV1: string
{
    case Report = 'report';
    case Validate = 'validate';
    case Investigate = 'investigate';
    case Decide = 'decide';
    case Audit = 'audit';
}
