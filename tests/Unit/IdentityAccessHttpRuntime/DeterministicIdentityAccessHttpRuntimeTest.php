<?php

namespace Tests\Unit\IdentityAccessHttpRuntime;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessSessionStore;
use App\Application\IdentityAccessHttp\DeterministicIdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpOperation;
use App\Application\IdentityAccessHttp\IdentityAccessHttpStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Argon2IdCredentialHashAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialRecord;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialSourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\LoginIdentitySourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicCredentialVerifier;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicLoginIdentityResolver;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionPolicyEvaluator;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionReduction;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\HmacSha256SessionSecretAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionConcurrencyCandidate;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeterministicIdentityAccessHttpRuntimeTest extends TestCase
{
    private const ACCOUNT = '81000000-0000-4000-8000-000000000001';

    private InMemorySessionStore $sessions;

    private DeterministicIdentityAccessHttpRuntime $runtime;

    protected function setUp(): void
    {
        $account = AccountId::fromString(self::ACCOUNT);
        $hash = password_hash('correct horse battery staple', PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);
        $source = new class($account, $hash) implements CredentialSourceV1, LoginIdentitySourceV1
        {
            public function __construct(private AccountId $account, private string $hash) {}

            public function byEmail(string $normalizedEmail): ?AccountId
            {
                return $normalizedEmail === 'owner@appart.sn' ? $this->account : null;
            }

            public function byPhone(string $normalizedPhone): ?AccountId
            {
                return $normalizedPhone === '+221771234567' ? $this->account : null;
            }

            public function credential(AccountId $accountId): ?CredentialRecord
            {
                return $accountId->equals($this->account) ? new CredentialRecord($this->hash, true) : null;
            }
        };
        $hashes = new Argon2IdCredentialHashAuthority;
        $secrets = new HmacSha256SessionSecretAuthority('test-v1', str_repeat('k', 32));
        $policy = new DeterministicSessionPolicyEvaluator;
        $this->sessions = new InMemorySessionStore;
        $this->runtime = new DeterministicIdentityAccessHttpRuntime(
            new DeterministicLoginIdentityResolver($source),
            new DeterministicCredentialVerifier($source, $hashes),
            $secrets,
            $policy,
            new DeterministicSessionReduction($secrets, $policy),
            $this->sessions,
            new InMemoryIdentityAccessOrchestrator,
        );
    }

    public function test_login_inspection_rotation_logout_and_replay_are_composed(): void
    {
        $at = new DateTimeImmutable('2026-08-10T08:00:00+00:00');
        $login = $this->runtime->execute($this->loginCommand('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'owner@appart.sn', 'correct horse battery staple', $at));
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $login->status);
        self::assertNotNull($login->sessionSecret);
        $inspection = $this->runtime->inspectSession($login->sessionSecret, $at->modify('+1 minute'));
        self::assertTrue($inspection->valid);
        self::assertInstanceOf(AuthenticatedSessionContext::class, $inspection->context);
        self::assertSame(self::ACCOUNT, $inspection->context->accountId->value);

        $rotation = $this->runtime->execute($this->authenticatedCommand(
            IdentityAccessHttpOperation::RenewSession,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            $at->modify('+30 minutes'),
            $inspection->context,
        ));
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $rotation->status);
        self::assertNotNull($rotation->sessionSecret);
        self::assertFalse($this->runtime->inspectSession($login->sessionSecret, $at->modify('+31 minutes'))->valid);
        $rotated = $this->runtime->inspectSession($rotation->sessionSecret, $at->modify('+31 minutes'));
        self::assertTrue($rotated->valid);
        self::assertNotNull($rotated->context);

        $logoutCommand = $this->authenticatedCommand(IdentityAccessHttpOperation::Logout, 'cccccccc-cccc-4ccc-8ccc-cccccccccccc', $at->modify('+32 minutes'), $rotated->context);
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $this->runtime->execute($logoutCommand)->status);
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $this->runtime->execute($logoutCommand)->status);
        self::assertFalse($this->runtime->inspectSession($rotation->sessionSecret, $at->modify('+33 minutes'))->valid);
    }

    public function test_missing_account_and_wrong_credential_are_indistinguishable(): void
    {
        $at = new DateTimeImmutable('2026-08-10T08:00:00+00:00');
        $missing = $this->runtime->execute($this->loginCommand('dddddddd-dddd-4ddd-8ddd-dddddddddddd', 'missing@appart.sn', 'correct horse battery staple', $at));
        $wrong = $this->runtime->execute($this->loginCommand('eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee', 'owner@appart.sn', 'wrong credential', $at));

        self::assertSame(IdentityAccessHttpStatus::GenericFailure, $missing->status);
        self::assertSame($missing->status, $wrong->status);
        self::assertSame($missing->publicData, $wrong->publicData);
    }

    public function test_absolute_expiration_and_sixth_login_revoke_the_oldest_session(): void
    {
        $at = new DateTimeImmutable('2026-08-10T08:00:00+00:00');
        $cookies = [];
        foreach (range(1, 6) as $ordinal) {
            $result = $this->runtime->execute($this->loginCommand(
                sprintf('ffffffff-ffff-4fff-8fff-%012d', $ordinal),
                'owner@appart.sn',
                'correct horse battery staple',
                $at->modify('+'.$ordinal.' minutes'),
            ));
            self::assertSame(IdentityAccessHttpStatus::Succeeded, $result->status);
            $cookies[] = $result->sessionSecret;
        }

        self::assertCount(5, $this->sessions->activeSessions(self::ACCOUNT));
        self::assertFalse($this->runtime->inspectSession((string) $cookies[0], $at->modify('+7 minutes'))->valid);
        self::assertFalse($this->runtime->inspectSession((string) $cookies[5], $at->modify('+9 hours'))->valid);
    }

    private function loginCommand(string $intent, string $identifier, string $credential, DateTimeImmutable $at): IdentityAccessHttpCommand
    {
        return new IdentityAccessHttpCommand(IdentityAccessHttpOperation::Login, $intent, [
            'identifier' => $identifier,
            'credential' => $credential,
        ], null, $at);
    }

    private function authenticatedCommand(IdentityAccessHttpOperation $operation, string $intent, DateTimeImmutable $at, AuthenticatedSessionContext $context): IdentityAccessHttpCommand
    {
        return new IdentityAccessHttpCommand($operation, $intent, [], $context->accountId, $at, $context);
    }
}

final class InMemorySessionStore implements IdentityAccessSessionStore
{
    /** @var array<string, OwnerPersistenceState> */
    private array $states = [];

    public function read(string $sessionId): ?OwnerPersistenceState
    {
        return $this->states[$sessionId] ?? null;
    }

    public function save(OwnerPersistenceState $state, int $expectedVersion): PersistenceWriteResult
    {
        $current = $this->states[$state->identity] ?? null;
        if ($current !== null && $current->intentId === $state->intentId) {
            return hash_equals($current->intentChecksum, $state->intentChecksum) ? PersistenceWriteResult::IdempotentReplay : PersistenceWriteResult::PersistenceRejected;
        }
        $currentVersion = $current === null ? 0 : $current->version;
        if ($currentVersion !== $expectedVersion || $state->version !== $expectedVersion + 1) {
            return PersistenceWriteResult::VersionConflict;
        }
        $this->states[$state->identity] = $state;

        return PersistenceWriteResult::Applied;
    }

    public function activeSessions(string $accountId): array
    {
        $sessions = [];
        foreach ($this->states as $state) {
            if (($state->values['account_id'] ?? null) === $accountId && ($state->values['state'] ?? null) === 'Active') {
                $sessions[] = new SessionConcurrencyCandidate($state->identity, new DateTimeImmutable((string) $state->values['original_issued_at']));
            }
        }

        return $sessions;
    }
}

final class InMemoryIdentityAccessOrchestrator implements IdentityAccessOrchestrator
{
    /** @var array<string, array{checksum:string,result:IdentityAccessOrchestrationResult}> */
    private array $results = [];

    public function authenticate(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function manageSession(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function recoverPassword(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function changeContact(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function swapClaim(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function mutateProfile(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function closeAccount(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    public function reopenAccount(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        return $this->execute($command, $work);
    }

    private function execute(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        $existing = $this->results[$command->intentId] ?? null;
        if ($existing !== null) {
            return new IdentityAccessOrchestrationResult($command->operation, hash_equals($existing['checksum'], $command->intentChecksum)
                ? IdentityAccessOrchestrationStatus::IdempotentReplay
                : IdentityAccessOrchestrationStatus::ReplayConflict);
        }
        $outcome = $work();
        $result = new IdentityAccessOrchestrationResult($command->operation, $outcome === IdentityAccessAtomicWorkResult::Applied
            ? IdentityAccessOrchestrationStatus::Applied
            : IdentityAccessOrchestrationStatus::Rejected);
        $this->results[$command->intentId] = ['checksum' => $command->intentChecksum, 'result' => $result];

        return $result;
    }
}
