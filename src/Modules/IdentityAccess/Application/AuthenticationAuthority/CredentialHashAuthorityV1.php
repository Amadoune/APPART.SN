<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;

interface CredentialHashAuthorityV1
{
    public const VERSION = 'credential-hash-v1';

    public function hash(#[\SensitiveParameter] string $plaintext): PasswordHash;

    public function verify(#[\SensitiveParameter] string $plaintext, #[\SensitiveParameter] string $encodedHash): CredentialHashVerification;
}
