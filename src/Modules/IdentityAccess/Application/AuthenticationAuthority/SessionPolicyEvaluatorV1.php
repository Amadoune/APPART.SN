<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

interface SessionPolicyEvaluatorV1
{
    public const VERSION = 'session-policy-v1';

    public function evaluate(SessionPolicyObservation $observation): SessionPolicyStatus;

    /** @param list<SessionConcurrencyCandidate> $activeSessions
     * @return list<string>
     */
    public function sessionsToRevokeForAdmission(array $activeSessions): array;
}
