<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

interface LoginIdentitySourceV1
{
    public function byEmail(string $normalizedEmail): ?AccountId;

    public function byPhone(string $normalizedPhone): ?AccountId;
}
