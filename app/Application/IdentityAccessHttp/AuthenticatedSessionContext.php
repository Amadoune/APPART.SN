<?php

namespace App\Application\IdentityAccessHttp;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use InvalidArgumentException;
use LogicException;

final readonly class AuthenticatedSessionContext
{
    public function __construct(public AccountId $accountId, public string $sessionId)
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D', $sessionId) !== 1) {
            throw new InvalidArgumentException('The authenticated Session identity is invalid.');
        }
    }

    public function __serialize(): array
    {
        throw new LogicException('Authenticated Session contexts are internal and non-serializable.');
    }
}
