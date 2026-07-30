<?php

namespace App\Application\IdentityAccessHttp;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class IdentityAccessSessionInspection
{
    private function __construct(
        public bool $valid,
        public ?AccountId $accountId,
    ) {}

    public static function valid(AccountId $accountId): self
    {
        return new self(true, $accountId);
    }

    public static function invalid(): self
    {
        return new self(false, null);
    }
}
