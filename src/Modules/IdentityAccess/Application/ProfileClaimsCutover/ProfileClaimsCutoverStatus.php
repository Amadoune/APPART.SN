<?php

namespace Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover;

enum ProfileClaimsCutoverStatus: string
{
    case Committed = 'Committed';
    case IdempotentReplay = 'IdempotentReplay';
    case Quarantined = 'Quarantined';
    case AlreadyCutOver = 'AlreadyCutOver';
    case RolledBack = 'RolledBack';
    case RollbackRejected = 'RollbackRejected';
    case PersistenceRejected = 'PersistenceRejected';
}
