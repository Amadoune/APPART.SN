<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

interface CredentialVerifierV1
{
    public function verify(AccountId $accountId, #[\SensitiveParameter] string $plaintext): CredentialVerificationResult;
}
