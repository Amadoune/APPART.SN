<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use DateTimeImmutable;

final readonly class SessionPolicyObservation
{
    public function __construct(
        public string $policyVersion,
        public string $state,
        public DateTimeImmutable $originalIssuedAt,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $lastSeenAt,
        public DateTimeImmutable $observedAt,
    ) {}
}
