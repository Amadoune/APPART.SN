<?php

namespace App\Application\IdentityAccessHttp;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class IdentityAccessSessionInspection
{
    private function __construct(
        public bool $valid,
        public ?AccountId $accountId,
        public ?AuthenticatedSessionContext $context,
    ) {}

    public static function valid(AuthenticatedSessionContext $context): self
    {
        return new self(true, $context->accountId, $context);
    }

    public static function invalid(): self
    {
        return new self(false, null, null);
    }
}
