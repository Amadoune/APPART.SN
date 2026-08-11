<?php

namespace Tests\Unit\IdentityAccessAuthenticationAuthority;

use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Argon2IdCredentialHashAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialRecord;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialSourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\LoginIdentitySourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerifierStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicCredentialVerifier;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicLoginIdentityResolver;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionPolicyEvaluator;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionReduction;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\HmacSha256SessionSecretAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\LoginIdentityResolutionStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionConcurrencyCandidate;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionPolicyObservation;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionPolicyStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionVerdict;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AuthenticationAuthorityTest extends TestCase
{
    public const ACCOUNT = '018f1f26-7cc2-7d7e-8d23-f76f0b3be221';

    private const SESSION = '018f1f26-7cc2-7d7e-8d23-f76f0b3be222';

    public function test_email_and_phone_are_normalized_and_resolved_without_account_id_login(): void
    {
        $source = new class implements LoginIdentitySourceV1
        {
            public function byEmail(string $normalizedEmail): ?AccountId
            {
                return $normalizedEmail === 'owner@appart.sn' ? AccountId::fromString(AuthenticationAuthorityTest::ACCOUNT) : null;
            }

            public function byPhone(string $normalizedPhone): ?AccountId
            {
                return $normalizedPhone === '+221771234567' ? AccountId::fromString(AuthenticationAuthorityTest::ACCOUNT) : null;
            }
        };
        $resolver = new DeterministicLoginIdentityResolver($source);

        self::assertSame(LoginIdentityResolutionStatus::Resolved, $resolver->resolve(' OWNER@APPART.SN ')->status);
        self::assertSame(LoginIdentityResolutionStatus::Resolved, $resolver->resolve('+221 77 123 45 67')->status);
        self::assertSame(LoginIdentityResolutionStatus::NotResolved, $resolver->resolve(self::ACCOUNT)->status);
    }

    public function test_argon2id_verification_rejection_and_success_only_rehash(): void
    {
        $hashes = new Argon2IdCredentialHashAuthority;
        $legacy = password_hash('correct horse battery staple', PASSWORD_BCRYPT);
        $accountId = AccountId::fromString(self::ACCOUNT);
        $source = new class($accountId, $legacy) implements CredentialSourceV1
        {
            public function __construct(private AccountId $id, private string $hash) {}

            public function credential(AccountId $accountId): ?CredentialRecord
            {
                return $accountId->equals($this->id) ? new CredentialRecord($this->hash, true) : null;
            }
        };
        $verifier = new DeterministicCredentialVerifier($source, $hashes);

        self::assertSame(CredentialVerifierStatus::Rejected, $verifier->verify($accountId, 'incorrect')->status);
        $verified = $verifier->verify($accountId, 'correct horse battery staple');
        self::assertSame(CredentialVerifierStatus::Verified, $verified->status);
        self::assertNotNull($verified->rehash);
        self::assertStringStartsWith('$argon2id$', $verified->rehash->persistenceValue()->revealForPersistence());
    }

    public function test_session_secret_is_random_256_bit_base64url_and_tamper_evident(): void
    {
        $authority = new HmacSha256SessionSecretAuthority('local-v1', str_repeat('k', 32));
        $first = $authority->issue(self::SESSION);
        $second = $authority->issue(self::SESSION);

        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $first->presented);
        self::assertNotSame($first->presented, $second->presented);
        self::assertTrue($authority->verify(self::SESSION, $first->presented, $first->storedProof));
        self::assertFalse($authority->verify(self::SESSION, $second->presented, $first->storedProof));
    }

    public function test_session_policy_enforces_rotation_idle_absolute_and_fail_closed_version(): void
    {
        $policy = new DeterministicSessionPolicyEvaluator;
        $created = new DateTimeImmutable('2026-08-10T08:00:00+00:00');
        $observation = static fn (string $version, string $at, string $lastSeen = '2026-08-10T08:20:00+00:00'): SessionPolicyObservation => new SessionPolicyObservation(
            $version, 'Active', $created, $created, new DateTimeImmutable($lastSeen), new DateTimeImmutable($at),
        );

        self::assertSame(SessionPolicyStatus::Valid, $policy->evaluate($observation('session-policy-v1', '2026-08-10T08:29:59+00:00')));
        self::assertSame(SessionPolicyStatus::RotationRequired, $policy->evaluate($observation('session-policy-v1', '2026-08-10T08:30:00+00:00')));
        self::assertSame(SessionPolicyStatus::IdleExpired, $policy->evaluate($observation('session-policy-v1', '2026-08-10T08:50:00+00:00')));
        self::assertSame(SessionPolicyStatus::AbsoluteExpired, $policy->evaluate($observation('session-policy-v1', '2026-08-10T16:00:00+00:00', '2026-08-10T15:59:00+00:00')));
        self::assertSame(SessionPolicyStatus::DependencyUnavailable, $policy->evaluate($observation('unknown', '2026-08-10T08:01:00+00:00')));

        $sessions = [];
        foreach (range(1, 5) as $ordinal) {
            $sessions[] = new SessionConcurrencyCandidate('session-'.$ordinal, $created->modify('+'.$ordinal.' minutes'));
        }
        self::assertSame(['session-1'], $policy->sessionsToRevokeForAdmission($sessions));
    }

    public function test_store_state_is_reduced_mechanically_and_replay_is_stable(): void
    {
        $secretAuthority = new HmacSha256SessionSecretAuthority('local-v1', str_repeat('k', 32));
        $secret = $secretAuthority->issue(self::SESSION);
        $state = new OwnerPersistenceState(self::SESSION, 1, self::ACCOUNT, hash('sha256', 'intent'), [
            'secret_hash' => $secret->storedProof,
            'policy_version' => 'session-policy-v1',
            'state' => 'Active',
            'original_issued_at' => '2026-08-10T08:00:00+00:00',
            'issued_at' => '2026-08-10T08:00:00+00:00',
            'last_seen_at' => '2026-08-10T08:10:00+00:00',
        ]);
        $reduction = new DeterministicSessionReduction($secretAuthority, new DeterministicSessionPolicyEvaluator);

        $first = $reduction->reduce($state, $secret->presented, new DateTimeImmutable('2026-08-10T08:20:00+00:00'));
        $replay = $reduction->reduce($state, $secret->presented, new DateTimeImmutable('2026-08-10T08:20:00+00:00'));
        self::assertSame(SessionVerdict::Valid, $first);
        self::assertSame($first, $replay);
        self::assertSame(SessionVerdict::InvalidSecret, $reduction->reduce($state, str_repeat('a', 43), new DateTimeImmutable('2026-08-10T08:20:00+00:00')));
    }
}
