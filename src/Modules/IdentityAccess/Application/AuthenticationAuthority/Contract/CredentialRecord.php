<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract;

final readonly class CredentialRecord
{
    public function __construct(#[\SensitiveParameter] public string $encodedHash, public bool $accountAvailable) {}

    public function __debugInfo(): array
    {
        return ['encodedHash' => '[REDACTED]', 'accountAvailable' => $this->accountAvailable];
    }
}
