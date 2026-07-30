<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusPersistence;

enum AccountStatusPersistenceWriteResult: string
{
    case Applied = 'applied';
    case AccountMissing = 'account_missing';
    case VersionConflict = 'version_conflict';
    case PersistenceRejected = 'persistence_rejected';
}
