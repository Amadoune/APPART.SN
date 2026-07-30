<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusPersistence;

enum AccountStatusPersistenceReadStatus: string
{
    case Found = 'found';
    case AccountMissing = 'account_missing';
    case LegacyUninitialized = 'legacy_uninitialized';
    case PersistenceRejected = 'persistence_rejected';
}
