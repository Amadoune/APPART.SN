<?php

namespace Appart\Modules\IdentityAccess\Application\Contract;

use Appart\Modules\IdentityAccess\Domain\Exception\ConcurrentAccountModification;
use Appart\Modules\IdentityAccess\Domain\Exception\DuplicateAccountIdentity;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

interface AccountRegistry
{
    /** Returns a detached Aggregate instance. */
    public function find(AccountId $id): ?Account;

    /**
     * Atomically enforces unique account id, email address and phone number.
     *
     * @throws DuplicateAccountIdentity
     */
    public function add(Account $account): void;

    /**
     * Saves only when the stored version equals the expected version.
     *
     * @throws ConcurrentAccountModification
     */
    public function save(Account $account, int $expectedVersion): void;
}
