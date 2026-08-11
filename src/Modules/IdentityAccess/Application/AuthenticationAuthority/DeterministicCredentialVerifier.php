<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialSourceV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Throwable;

final readonly class DeterministicCredentialVerifier implements CredentialVerifierV1
{
    public function __construct(private CredentialSourceV1 $source, private CredentialHashAuthorityV1 $hashes) {}

    public function verify(AccountId $accountId, #[\SensitiveParameter] string $plaintext): CredentialVerificationResult
    {
        try {
            $record = $this->source->credential($accountId);
            if ($record === null) {
                return CredentialVerificationResult::status(CredentialVerifierStatus::Rejected);
            }
            if (! $record->accountAvailable) {
                return CredentialVerificationResult::status(CredentialVerifierStatus::AccountUnavailable);
            }
            $verification = $this->hashes->verify($plaintext, $record->encodedHash);
            if (! $verification->verified) {
                return CredentialVerificationResult::status(CredentialVerifierStatus::Rejected);
            }

            return CredentialVerificationResult::status(
                CredentialVerifierStatus::Verified,
                $verification->rehashRequired ? $this->hashes->hash($plaintext) : null,
            );
        } catch (Throwable) {
            return CredentialVerificationResult::status(CredentialVerifierStatus::DependencyUnavailable);
        }
    }
}
