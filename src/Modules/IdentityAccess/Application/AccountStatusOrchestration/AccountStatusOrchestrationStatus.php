<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration;

enum AccountStatusOrchestrationStatus: string
{
    case Applied = 'applied';
    case AlreadyInState = 'already_in_state';
    case AccountMissing = 'account_missing';
    case VersionConflict = 'version_conflict';
    case InvalidContext = 'invalid_context';
    case PersistenceRejected = 'persistence_rejected';
    case PersistenceCorrupted = 'persistence_corrupted';
    case InspectionCorrupted = 'inspection_corrupted';
}
