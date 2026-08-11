<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

interface SessionSecretAuthorityV1
{
    public const VERSION = 'session-secret-hmac-sha256-v1';

    public function issue(string $sessionId): SessionSecret;

    public function verify(string $sessionId, #[\SensitiveParameter] string $presented, #[\SensitiveParameter] string $storedProof): bool;
}
