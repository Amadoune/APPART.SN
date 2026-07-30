<?php

namespace Tests\Unit\Modules\IdentityAccess\Support;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Exception\ConcurrentAccountModification;
use Appart\Modules\IdentityAccess\Domain\Exception\DuplicateAccountIdentity;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final class FakeAccountRegistry implements AccountRegistry
{
    /** @var array<string, Account> */
    private array $accounts = [];

    private bool $failNextSave = false;

    public function find(AccountId $id): ?Account
    {
        return isset($this->accounts[$id->value]) ? clone $this->accounts[$id->value] : null;
    }

    public function add(Account $candidate): void
    {
        if (isset($this->accounts[$candidate->id()->value])) {
            throw DuplicateAccountIdentity::accountId();
        }
        foreach ($this->accounts as $account) {
            if ($account->email()->equals($candidate->email())) {
                throw DuplicateAccountIdentity::email();
            }
            if ($account->phone()->equals($candidate->phone())) {
                throw DuplicateAccountIdentity::phone();
            }
        }
        $this->accounts[$candidate->id()->value] = clone $candidate;
    }

    public function save(Account $account, int $expectedVersion): void
    {
        $stored = $this->accounts[$account->id()->value] ?? null;
        if ($this->failNextSave || $stored === null || $stored->version() !== $expectedVersion) {
            $this->failNextSave = false;
            throw new ConcurrentAccountModification;
        }
        $this->accounts[$account->id()->value] = clone $account;
    }

    public function failNextSave(): void
    {
        $this->failNextSave = true;
    }
}
