<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

final readonly class DeterministicSessionPolicyEvaluator implements SessionPolicyEvaluatorV1
{
    public function evaluate(SessionPolicyObservation $observation): SessionPolicyStatus
    {
        if ($observation->policyVersion !== self::VERSION) {
            return SessionPolicyStatus::DependencyUnavailable;
        }
        if ($observation->state !== 'Active') {
            return SessionPolicyStatus::Revoked;
        }
        if ($observation->observedAt >= $observation->originalIssuedAt->modify('+8 hours')) {
            return SessionPolicyStatus::AbsoluteExpired;
        }
        if ($observation->observedAt >= $observation->lastSeenAt->modify('+30 minutes')) {
            return SessionPolicyStatus::IdleExpired;
        }
        if ($observation->observedAt >= $observation->issuedAt->modify('+30 minutes')) {
            return SessionPolicyStatus::RotationRequired;
        }

        return SessionPolicyStatus::Valid;
    }

    public function sessionsToRevokeForAdmission(array $activeSessions): array
    {
        usort($activeSessions, static fn (SessionConcurrencyCandidate $left, SessionConcurrencyCandidate $right): int => [
            $left->originalIssuedAt->format('U.u'),
            $left->sessionId,
        ] <=> [
            $right->originalIssuedAt->format('U.u'),
            $right->sessionId,
        ]);

        return array_map(
            static fn (SessionConcurrencyCandidate $candidate): string => $candidate->sessionId,
            array_slice($activeSessions, 0, max(0, count($activeSessions) - 4)),
        );
    }
}
