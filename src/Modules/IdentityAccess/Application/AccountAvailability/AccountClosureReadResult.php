<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

final readonly class AccountClosureReadResult
{
    private function __construct(
        public AccountClosureReadStatus $status,
        public ?AccountClosureState $state,
        public ?int $version,
    ) {}

    public static function found(AccountClosureState $state, int $version): self
    {
        return new self(AccountClosureReadStatus::Found, $state, $version);
    }

    public static function legacyOpen(): self
    {
        return new self(AccountClosureReadStatus::LegacyOpen, AccountClosureState::Open, 0);
    }

    public static function persistenceRejected(): self
    {
        return new self(AccountClosureReadStatus::PersistenceRejected, null, null);
    }
}
