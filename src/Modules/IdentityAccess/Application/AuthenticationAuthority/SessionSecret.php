<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

final readonly class SessionSecret
{
    public function __construct(#[\SensitiveParameter] public string $presented, #[\SensitiveParameter] public string $storedProof) {}

    public function __debugInfo(): array
    {
        return ['presented' => '[REDACTED]', 'storedProof' => '[REDACTED]'];
    }
}
