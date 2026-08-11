<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;

final readonly class Argon2IdCredentialHashAuthority implements CredentialHashAuthorityV1
{
    private const OPTIONS = ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1];

    public function hash(#[\SensitiveParameter] string $plaintext): PasswordHash
    {
        $encoded = password_hash($plaintext, PASSWORD_ARGON2ID, self::OPTIONS);

        return PasswordHash::fromString($encoded);
    }

    public function verify(#[\SensitiveParameter] string $plaintext, #[\SensitiveParameter] string $encodedHash): CredentialHashVerification
    {
        $info = password_get_info($encodedHash);
        if (($info['algoName'] ?? 'unknown') === 'unknown') {
            return new CredentialHashVerification(false, false);
        }
        $verified = password_verify($plaintext, $encodedHash);

        return new CredentialHashVerification(
            $verified,
            $verified && password_needs_rehash($encodedHash, PASSWORD_ARGON2ID, self::OPTIONS),
        );
    }
}
