<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence;

enum PersistenceWriteResult: string
{
    case Applied = 'Applied';
    case IdempotentReplay = 'IdempotentReplay';
    case VersionConflict = 'VersionConflict';
    case IdentityConflict = 'IdentityConflict';
    case PersistenceRejected = 'PersistenceRejected';
}
