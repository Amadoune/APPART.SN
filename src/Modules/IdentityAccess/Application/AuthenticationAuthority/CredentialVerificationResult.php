<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;

final readonly class CredentialVerificationResult
{
    private function __construct(public CredentialVerifierStatus $status, public ?PasswordHash $rehash) {}

    public static function status(CredentialVerifierStatus $status, ?PasswordHash $rehash = null): self
    {
        return new self($status, $rehash);
    }
}
