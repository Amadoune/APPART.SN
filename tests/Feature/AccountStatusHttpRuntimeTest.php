<?php

namespace Tests\Feature;

use App\Http\AccountStatusHttpResultPresenter;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AccountStatusHttpRuntimeTest extends TestCase
{
    private const string ACCOUNT_ID = '49e00000-0000-4000-8000-000000000004';

    private const string TOKEN = 'account-status-http-test-token-0000000000000001';

    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'account_status_http.bearer_token' => self::TOKEN,
        ]);
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->app->instance(PDO::class, $this->connection);
        (new PostgreSqlAccountRepository(
            $this->connection,
            new AccountPersistenceMapper,
        ))->add($this->account());
    }

    public function test_suspend_and_reactivate_are_the_only_authenticated_atomic_operations(): void
    {
        $this->withHeaders($this->authorizedHeaders())
            ->postJson(
                '/api/account-statuses/'.self::ACCOUNT_ID.'/suspend',
                $this->payload('active', 0),
            )
            ->assertOk()
            ->assertExactJson([
                'code' => 'account_status.applied',
                'status' => 'applied',
                'state' => 'suspended',
            ]);

        $this->withHeaders($this->authorizedHeaders())
            ->postJson(
                '/api/account-statuses/'.self::ACCOUNT_ID.'/reactivate',
                $this->payload('suspended', 1),
            )
            ->assertOk()
            ->assertExactJson([
                'code' => 'account_status.applied',
                'status' => 'applied',
                'state' => 'active',
            ]);

        self::assertSame(
            2,
            (int) $this->connection->query(
                'SELECT count(*) FROM identity_access.public_projection_outbox_messages',
            )->fetchColumn(),
        );
    }

    public function test_authentication_and_scope_authorization_are_explicit(): void
    {
        $uri = '/api/account-statuses/'.self::ACCOUNT_ID.'/suspend';

        $this->postJson($uri, $this->payload('active', 0))
            ->assertUnauthorized()
            ->assertExactJson([
                'code' => 'account_status.authentication_required',
            ]);

        $this->withToken(self::TOKEN)
            ->withHeader('X-Account-Status-Scope', 'identity_access.read')
            ->postJson($uri, $this->payload('active', 0))
            ->assertForbidden()
            ->assertExactJson([
                'code' => 'account_status.authorization_denied',
            ]);
    }

    public function test_validation_rejects_unknown_actions_sensitive_fields_and_invalid_versions(): void
    {
        $uri = '/api/account-statuses/'.self::ACCOUNT_ID.'/suspend';
        $payload = $this->payload('active', 0) + [
            'action' => 'delete',
            'historical_version' => 99,
            'credential' => 'secret',
            'routingProof' => str_repeat('a', 64),
        ];

        $this->withHeaders($this->authorizedHeaders())
            ->postJson($uri, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('_request');

        $invalid = $this->payload('active', 0);
        $invalid['expectedVersion'] = -1;
        $this->withHeaders($this->authorizedHeaders())
            ->postJson($uri, $invalid)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expectedVersion');
    }

    #[DataProvider('statusMatrix')]
    public function test_presenter_matrix_is_closed_and_minimal(
        AccountStatusOrchestrationStatus $status,
        int $http,
        string $code,
    ): void {
        $response = (new AccountStatusHttpResultPresenter)->response(
            new AccountStatusOrchestrationResult($status, null),
        );
        $payload = $response->getData(true);

        self::assertSame($http, $response->getStatusCode());
        self::assertSame($code, $payload['code']);
        self::assertSame($status->value, $payload['status']);
        self::assertSame(['code', 'status'], array_keys($payload));
    }

    /** @return iterable<string, array{AccountStatusOrchestrationStatus, int, string}> */
    public static function statusMatrix(): iterable
    {
        yield 'applied' => [AccountStatusOrchestrationStatus::Applied, 200, 'account_status.applied'];
        yield 'already in state' => [AccountStatusOrchestrationStatus::AlreadyInState, 200, 'account_status.already_in_state'];
        yield 'account missing' => [AccountStatusOrchestrationStatus::AccountMissing, 404, 'account_status.not_found'];
        yield 'version conflict' => [AccountStatusOrchestrationStatus::VersionConflict, 409, 'account_status.version_conflict'];
        yield 'invalid context' => [AccountStatusOrchestrationStatus::InvalidContext, 422, 'account_status.invalid_context'];
        yield 'persistence rejected' => [AccountStatusOrchestrationStatus::PersistenceRejected, 409, 'account_status.persistence_rejected'];
        yield 'persistence corrupted' => [AccountStatusOrchestrationStatus::PersistenceCorrupted, 503, 'account_status.persistence_unavailable'];
        yield 'inspection corrupted' => [AccountStatusOrchestrationStatus::InspectionCorrupted, 503, 'account_status.inspection_unavailable'];
    }

    /** @return array<string, string> */
    private function authorizedHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.self::TOKEN,
            'X-Account-Status-Scope' => 'identity_access.account_status.manage',
        ];
    }

    /** @return array<string, int|string> */
    private function payload(string $state, int $version): array
    {
        return [
            'currentState' => $state,
            'contextVersion' => 1,
            'expectedVersion' => $version,
            'observedVersion' => $version,
            'actorId' => 'operator-49l',
            'occurredAt' => '2026-07-26T18:00:00.000000Z',
            'recordedAt' => '2026-07-26T18:00:01.000000Z',
            'intentId' => 'intent-49l-'.$version,
        ];
    }

    private function account(): Account
    {
        $at = new DateTimeImmutable('2026-07-26T17:00:00+00:00');
        $account = Account::register(
            AccountId::fromString(self::ACCOUNT_ID),
            EmailAddress::fromString('http-account-status@example.test'),
            PhoneNumber::fromString('+221770049004'),
            PersonName::fromString('HTTP Account Status'),
            PasswordHash::fromString('$generic$v=1$salt-test$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $at->modify('+1 hour'),
            $at,
        );
        $account->releaseEvents();

        return $account;
    }
}
