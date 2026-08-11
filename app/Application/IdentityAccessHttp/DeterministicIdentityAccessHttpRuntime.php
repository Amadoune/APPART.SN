<?php

namespace App\Application\IdentityAccessHttp;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessSessionStore;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerifierStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerifierV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\LoginIdentityResolutionStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\LoginIdentityResolverV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionPolicyEvaluatorV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionReductionV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionSecretAuthorityV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionVerdict;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicOperation;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Throwable;

final readonly class DeterministicIdentityAccessHttpRuntime implements IdentityAccessHttpRuntime
{
    private const SESSION_SECONDS = 28800;

    public function __construct(
        private LoginIdentityResolverV1 $identities,
        private CredentialVerifierV1 $credentials,
        private SessionSecretAuthorityV1 $secrets,
        private SessionPolicyEvaluatorV1 $policy,
        private SessionReductionV1 $reduction,
        private IdentityAccessSessionStore $sessions,
        private IdentityAccessOrchestrator $orchestrator,
    ) {}

    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        try {
            return match ($command->operation) {
                IdentityAccessHttpOperation::Login => $this->login($command),
                IdentityAccessHttpOperation::Logout => $this->logout($command),
                IdentityAccessHttpOperation::RenewSession => $this->rotate($command),
                default => new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable),
            };
        } catch (Throwable) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
        }
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        $parts = $this->cookieParts($secret);
        if ($parts === null) {
            return IdentityAccessSessionInspection::invalid();
        }
        [$sessionId, $presented] = $parts;
        $state = $this->sessions->read($sessionId);
        $verdict = $this->reduction->reduce($state, $presented, $at);
        if (! in_array($verdict, [SessionVerdict::Valid, SessionVerdict::RotationRequired], true)
            || $state === null
            || ! is_string($state->values['account_id'] ?? null)) {
            return IdentityAccessSessionInspection::invalid();
        }

        return IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(
            AccountId::fromString($state->values['account_id']),
            $sessionId,
        ));
    }

    private function login(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        $identifier = $command->input['identifier'] ?? null;
        $credential = $command->input['credential'] ?? null;
        if (! is_string($identifier) || ! is_string($credential)) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::GenericFailure);
        }
        $identity = $this->identities->resolve($identifier);
        if ($identity->status === LoginIdentityResolutionStatus::DependencyUnavailable) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
        }
        if ($identity->status !== LoginIdentityResolutionStatus::Resolved || $identity->accountId === null) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::GenericFailure);
        }
        $verified = $this->credentials->verify($identity->accountId, $credential);
        if ($verified->status === CredentialVerifierStatus::DependencyUnavailable) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
        }
        if ($verified->status !== CredentialVerifierStatus::Verified) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::GenericFailure);
        }

        $sessionId = Uuid::uuid7()->toString();
        $secret = $this->secrets->issue($sessionId);
        $atomic = $this->atomic($command, $identity->accountId);
        $result = $this->orchestrator->manageSession($atomic, function () use ($command, $identity, $sessionId, $secret, $atomic): IdentityAccessAtomicWorkResult {
            $active = $this->sessions->activeSessions($identity->accountId->value);
            foreach ($this->policy->sessionsToRevokeForAdmission($active) as $revokedId) {
                $current = $this->sessions->read($revokedId);
                if ($current === null || $this->sessions->save($this->mutated($current, 'Revoked', null, $atomic), $current->version) !== PersistenceWriteResult::Applied) {
                    return IdentityAccessAtomicWorkResult::Rejected;
                }
            }

            return $this->sessions->save($this->newSession($sessionId, $identity->accountId, $secret->storedProof, $command->requestedAt, $atomic), 0) === PersistenceWriteResult::Applied
                ? IdentityAccessAtomicWorkResult::Applied
                : IdentityAccessAtomicWorkResult::Rejected;
        });
        if ($result->status !== IdentityAccessOrchestrationStatus::Applied) {
            return new IdentityAccessHttpResult($result->status === IdentityAccessOrchestrationStatus::RolledBack
                ? IdentityAccessHttpStatus::Unavailable
                : IdentityAccessHttpStatus::Conflict);
        }

        return new IdentityAccessHttpResult(
            IdentityAccessHttpStatus::Succeeded,
            [],
            's1.'.$sessionId.'.'.$secret->presented,
            self::SESSION_SECONDS,
        );
    }

    private function logout(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        return $this->mutateAuthenticatedSession($command, false);
    }

    private function rotate(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        return $this->mutateAuthenticatedSession($command, true);
    }

    private function mutateAuthenticatedSession(IdentityAccessHttpCommand $command, bool $rotate): IdentityAccessHttpResult
    {
        $context = $command->authenticatedSession;
        if ($context === null || $command->authenticatedAccount === null || ! $context->accountId->equals($command->authenticatedAccount)) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Forbidden);
        }
        $current = $this->sessions->read($context->sessionId);
        if ($current === null || ($current->values['account_id'] ?? null) !== $context->accountId->value) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Forbidden);
        }
        $atomic = $this->atomic($command, $context->accountId);
        $newSessionId = $rotate ? Uuid::uuid7()->toString() : null;
        $newSecret = $newSessionId === null ? null : $this->secrets->issue($newSessionId);
        $result = $this->orchestrator->manageSession($atomic, function () use ($command, $current, $rotate, $newSessionId, $newSecret, $atomic): IdentityAccessAtomicWorkResult {
            $fresh = $this->sessions->read($current->identity);
            if ($fresh === null || $fresh->version !== $current->version || ($fresh->values['state'] ?? null) !== 'Active') {
                return IdentityAccessAtomicWorkResult::Rejected;
            }
            $state = $rotate ? 'Rotated' : 'Revoked';
            if ($this->sessions->save($this->mutated($fresh, $state, $newSessionId, $atomic), $fresh->version) !== PersistenceWriteResult::Applied) {
                return IdentityAccessAtomicWorkResult::Rejected;
            }
            if (! $rotate) {
                return IdentityAccessAtomicWorkResult::Applied;
            }

            return $this->sessions->save($this->newSession(
                $newSessionId,
                AccountId::fromString((string) $fresh->values['account_id']),
                $newSecret->storedProof,
                $command->requestedAt,
                $atomic,
                (string) $fresh->values['original_issued_at'],
            ), 0) === PersistenceWriteResult::Applied ? IdentityAccessAtomicWorkResult::Applied : IdentityAccessAtomicWorkResult::Rejected;
        });
        if (! in_array($result->status, [IdentityAccessOrchestrationStatus::Applied, IdentityAccessOrchestrationStatus::IdempotentReplay], true)) {
            return new IdentityAccessHttpResult($result->status === IdentityAccessOrchestrationStatus::RolledBack
                ? IdentityAccessHttpStatus::Unavailable
                : IdentityAccessHttpStatus::Conflict);
        }
        if ($rotate && $result->status === IdentityAccessOrchestrationStatus::Applied) {
            return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Succeeded, [], 's1.'.$newSessionId.'.'.$newSecret->presented, self::SESSION_SECONDS);
        }

        return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Succeeded);
    }

    private function atomic(IdentityAccessHttpCommand $command, AccountId $accountId): IdentityAccessAtomicCommand
    {
        return new IdentityAccessAtomicCommand(
            $command->intentId,
            hash('sha256', implode('|', [$command->operation->value, $accountId->value, $command->requestedAt->format('Y-m-d\TH:i:s.uP')])),
            $accountId,
            IdentityAccessAtomicOperation::Session,
            $command->requestedAt,
        );
    }

    private function newSession(string $sessionId, AccountId $accountId, string $proof, DateTimeImmutable $at, IdentityAccessAtomicCommand $atomic, ?string $originalIssuedAt = null): OwnerPersistenceState
    {
        $issued = $at->format('Y-m-d\TH:i:s.uP');
        $original = $originalIssuedAt ?? $issued;

        return new OwnerPersistenceState($sessionId, 1, $atomic->intentId, $atomic->intentChecksum, [
            'account_id' => $accountId->value,
            'secret_hash' => $proof,
            'state' => 'Active',
            'policy_version' => SessionPolicyEvaluatorV1::VERSION,
            'original_issued_at' => $original,
            'idle_expires_at' => $at->modify('+30 minutes')->format('Y-m-d\TH:i:s.uP'),
            'absolute_expires_at' => (new DateTimeImmutable($original))->modify('+8 hours')->format('Y-m-d\TH:i:s.uP'),
            'issued_at' => $issued,
            'expires_at' => (new DateTimeImmutable($original))->modify('+8 hours')->format('Y-m-d\TH:i:s.uP'),
            'last_seen_at' => $issued,
            'rotated_to' => null,
            'device_reference' => null,
            'issued_checkpoint' => 0,
        ]);
    }

    private function mutated(OwnerPersistenceState $current, string $state, ?string $rotatedTo, IdentityAccessAtomicCommand $atomic): OwnerPersistenceState
    {
        return new OwnerPersistenceState($current->identity, $current->version + 1, $atomic->intentId, $atomic->intentChecksum, array_merge($current->values, [
            'state' => $state,
            'rotated_to' => $rotatedTo,
        ]));
    }

    /** @return array{string, string}|null */
    private function cookieParts(string $cookie): ?array
    {
        if (preg_match('/^s1\.([0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})\.([A-Za-z0-9_-]{43})$/D', $cookie, $matches) !== 1) {
            return null;
        }

        return [$matches[1], $matches[2]];
    }
}
