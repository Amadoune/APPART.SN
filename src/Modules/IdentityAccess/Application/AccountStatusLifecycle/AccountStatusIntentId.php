<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

final readonly class AccountStatusIntentId
{
    public function __construct(public string $value) {}

    public function isValid(): bool
    {
        return trim($this->value) !== '';
    }
}
