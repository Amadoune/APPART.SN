<?php

namespace App\Application\IdentityAccessHttp\Contract;

use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionConcurrencyCandidate;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;

interface IdentityAccessSessionStore
{
    public function read(string $sessionId): ?OwnerPersistenceState;

    public function save(OwnerPersistenceState $state, int $expectedVersion): PersistenceWriteResult;

    /** @return list<SessionConcurrencyCandidate> */
    public function activeSessions(string $accountId): array;
}
