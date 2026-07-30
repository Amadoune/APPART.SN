<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\Contract;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;

interface OwnerPersistenceStore
{
    public function read(string $identity): ?OwnerPersistenceState;

    public function save(OwnerPersistenceState $state, int $expectedVersion): PersistenceWriteResult;
}
