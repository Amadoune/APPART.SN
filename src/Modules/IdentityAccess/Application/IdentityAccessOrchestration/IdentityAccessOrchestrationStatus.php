<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration;

enum IdentityAccessOrchestrationStatus: string
{
    case Applied = 'Applied';
    case Rejected = 'Rejected';
    case IdempotentReplay = 'IdempotentReplay';
    case ReplayConflict = 'ReplayConflict';
    case RolledBack = 'RolledBack';
}
