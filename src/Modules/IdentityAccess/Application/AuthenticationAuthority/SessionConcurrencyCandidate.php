<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use DateTimeImmutable;

final readonly class SessionConcurrencyCandidate
{
    public function __construct(public string $sessionId, public DateTimeImmutable $originalIssuedAt) {}
}
