<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

final readonly class AccountStatusVersion
{
    public function __construct(public int $value) {}

    public function isValid(): bool
    {
        return $this->value >= 0;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
