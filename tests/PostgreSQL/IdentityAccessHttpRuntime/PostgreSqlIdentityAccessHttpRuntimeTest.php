<?php

namespace Tests\PostgreSQL\IdentityAccessHttpRuntime;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessSessionStore;
use App\Application\IdentityAccessHttp\DeterministicIdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpOperation;
use App\Application\IdentityAccessHttp\IdentityAccessHttpStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerificationResult;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerifierStatus;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerifierV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionPolicyEvaluator;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionReduction;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\HmacSha256SessionSecretAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\LoginIdentityResolution;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\LoginIdentityResolverV1;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\DeterministicIdentityAccessOrchestrator;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityAccessAtomicTransaction;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlSessionStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlIdentityAccessHttpRuntimeTest extends TestCase
{
    private const ACCOUNT = '82000000-0000-4000-8000-000000000001';

    private static PDO $connection;

    private DeterministicIdentityAccessHttpRuntime $runtime;

    private IdentityAccessSessionStore $sessions;

    public static function setUpBeforeClass(): void
    {
        self::$connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate(self::$connection);
    }

    protected function setUp(): void
    {
        PostgreSqlTestEnvironment::reset(self::$connection);
        $account = AccountId::fromString(self::ACCOUNT);
        $resolver = new class($account) implements LoginIdentityResolverV1
        {
            public function __construct(private AccountId $account) {}

            public function resolve(string $identifier): LoginIdentityResolution
            {
                return $identifier === 'owner@appart.sn' ? LoginIdentityResolution::resolved($this->account) : LoginIdentityResolution::notResolved();
            }
        };
        $verifier = new class implements CredentialVerifierV1
        {
            public function verify(AccountId $accountId, #[\SensitiveParameter] string $plaintext): CredentialVerificationResult
            {
                return CredentialVerificationResult::status($plaintext === 'correct horse battery staple'
                    ? CredentialVerifierStatus::Verified
                    : CredentialVerifierStatus::Rejected);
            }
        };
        $secrets = new HmacSha256SessionSecretAuthority('test-v1', str_repeat('k', 32));
        $policy = new DeterministicSessionPolicyEvaluator;
        $this->sessions = new PostgreSqlSessionStore(self::$connection, new IdentityAccessCompletionPersistenceMapper);
        $this->runtime = new DeterministicIdentityAccessHttpRuntime(
            $resolver,
            $verifier,
            $secrets,
            $policy,
            new DeterministicSessionReduction($secrets, $policy),
            $this->sessions,
            new DeterministicIdentityAccessOrchestrator(new PostgreSqlIdentityAccessAtomicTransaction(self::$connection)),
        );
    }

    public function test_login_rotation_logout_replay_and_concurrency_are_atomic(): void
    {
        $at = new DateTimeImmutable('2026-08-10T08:00:00+00:00');
        $cookies = [];
        foreach (range(1, 6) as $ordinal) {
            $login = $this->runtime->execute(new IdentityAccessHttpCommand(
                IdentityAccessHttpOperation::Login,
                sprintf('83000000-0000-4000-8000-%012d', $ordinal),
                ['identifier' => 'owner@appart.sn', 'credential' => 'correct horse battery staple'],
                null,
                $at->modify('+'.$ordinal.' minutes'),
            ));
            self::assertSame(IdentityAccessHttpStatus::Succeeded, $login->status);
            self::assertNotNull($login->sessionSecret);
            $cookies[] = $login->sessionSecret;
        }
        self::assertCount(5, $this->sessions->activeSessions(self::ACCOUNT));
        self::assertFalse($this->runtime->inspectSession($cookies[0], $at->modify('+7 minutes'))->valid);
        $inspection = $this->runtime->inspectSession($cookies[5], $at->modify('+7 minutes'));
        self::assertTrue($inspection->valid);
        self::assertNotNull($inspection->context);

        $rotate = $this->runtime->execute(new IdentityAccessHttpCommand(
            IdentityAccessHttpOperation::RenewSession,
            '84000000-0000-4000-8000-000000000001',
            [],
            $inspection->context->accountId,
            $at->modify('+31 minutes'),
            $inspection->context,
        ));
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $rotate->status);
        self::assertNotNull($rotate->sessionSecret);
        $rotated = $this->runtime->inspectSession($rotate->sessionSecret, $at->modify('+32 minutes'));
        self::assertNotNull($rotated->context);

        $logout = new IdentityAccessHttpCommand(IdentityAccessHttpOperation::Logout, '85000000-0000-4000-8000-000000000001', [], $rotated->context->accountId, $at->modify('+33 minutes'), $rotated->context);
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $this->runtime->execute($logout)->status);
        self::assertSame(IdentityAccessHttpStatus::Succeeded, $this->runtime->execute($logout)->status);
        self::assertFalse($this->runtime->inspectSession($rotate->sessionSecret, $at->modify('+34 minutes'))->valid);
    }
}
