<?php

namespace Tests\PostgreSQL\IdentityAccessCompletion;

use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\Contract\ProfileClaimsSeedProtector;

final readonly class DeterministicTestSeedProtector implements ProfileClaimsSeedProtector
{
    public function protect(string $normalizedValue): string
    {
        return 'protected:'.hash('sha256', $normalizedValue);
    }

    public function fingerprint(string $normalizedValue): string
    {
        return hash_hmac('sha256', $normalizedValue, 'test-only-identity-claim-key');
    }
}
