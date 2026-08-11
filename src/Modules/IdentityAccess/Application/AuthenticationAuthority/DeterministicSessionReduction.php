<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicSessionReduction implements SessionReductionV1
{
    public function __construct(private SessionSecretAuthorityV1 $secrets, private SessionPolicyEvaluatorV1 $policy) {}

    public function reduce(?OwnerPersistenceState $state, #[\SensitiveParameter] string $presentedSecret, DateTimeImmutable $observedAt): SessionVerdict
    {
        if ($state === null) {
            return SessionVerdict::Missing;
        }

        try {
            $values = $state->values;
            foreach (['secret_hash', 'policy_version', 'state', 'original_issued_at', 'issued_at', 'last_seen_at'] as $required) {
                if (! is_string($values[$required] ?? null)) {
                    return SessionVerdict::DependencyUnavailable;
                }
            }
            if (! $this->secrets->verify($state->identity, $presentedSecret, $values['secret_hash'])) {
                return SessionVerdict::InvalidSecret;
            }
            $status = $this->policy->evaluate(new SessionPolicyObservation(
                $values['policy_version'],
                $values['state'],
                new DateTimeImmutable($values['original_issued_at']),
                new DateTimeImmutable($values['issued_at']),
                new DateTimeImmutable($values['last_seen_at']),
                $observedAt,
            ));

            return match ($status) {
                SessionPolicyStatus::Valid => SessionVerdict::Valid,
                SessionPolicyStatus::RotationRequired => SessionVerdict::RotationRequired,
                SessionPolicyStatus::IdleExpired, SessionPolicyStatus::AbsoluteExpired => SessionVerdict::Expired,
                SessionPolicyStatus::Revoked => SessionVerdict::Revoked,
                SessionPolicyStatus::DependencyUnavailable => SessionVerdict::DependencyUnavailable,
            };
        } catch (Throwable) {
            return SessionVerdict::DependencyUnavailable;
        }
    }
}
