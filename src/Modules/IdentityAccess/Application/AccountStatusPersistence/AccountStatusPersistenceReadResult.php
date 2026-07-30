<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusPersistence;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class AccountStatusPersistenceReadResult
{
    private function __construct(
        public AccountId $accountId,
        public AccountStatusPersistenceReadStatus $status,
        public ?AccountStatusStoredState $snapshot,
        public ?AccountStatusState $legacyState,
    ) {}

    public static function found(AccountStatusStoredState $snapshot): self
    {
        return new self($snapshot->accountId, AccountStatusPersistenceReadStatus::Found, $snapshot, null);
    }

    public static function accountMissing(AccountId $accountId): self
    {
        return new self($accountId, AccountStatusPersistenceReadStatus::AccountMissing, null, null);
    }

    public static function legacyUninitialized(AccountId $accountId, AccountStatusState $state): self
    {
        return new self($accountId, AccountStatusPersistenceReadStatus::LegacyUninitialized, null, $state);
    }

    public static function persistenceRejected(AccountId $accountId): self
    {
        return new self($accountId, AccountStatusPersistenceReadStatus::PersistenceRejected, null, null);
    }
}
