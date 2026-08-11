<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use InvalidArgumentException;

final readonly class HmacSha256SessionSecretAuthority implements SessionSecretAuthorityV1
{
    public function __construct(private string $keyId, #[\SensitiveParameter] private string $key)
    {
        if (preg_match('/^[a-z0-9][a-z0-9-]{0,31}$/', $keyId) !== 1 || strlen($key) < 32) {
            throw new InvalidArgumentException('A versioned key id and at least 256 bits of key material are required.');
        }
    }

    public function issue(string $sessionId): SessionSecret
    {
        $secret = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return new SessionSecret($secret, $this->proof($sessionId, $secret));
    }

    public function verify(string $sessionId, #[\SensitiveParameter] string $presented, #[\SensitiveParameter] string $storedProof): bool
    {
        if (preg_match('/^[A-Za-z0-9_-]{43}$/D', $presented) !== 1) {
            return false;
        }

        return hash_equals($storedProof, $this->proof($sessionId, $presented));
    }

    private function proof(string $sessionId, #[\SensitiveParameter] string $secret): string
    {
        return self::VERSION.'.'.$this->keyId.'.'.hash_hmac('sha256', $sessionId."\0".$secret, $this->key);
    }
}
