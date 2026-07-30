<?php

namespace Tests\PostgreSQL\AccountStatusPersistence;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\DeterministicAccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAccountStatusOrchestratorTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAccountRepository $accounts;

    private DeterministicAccountStatusOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->accounts = new PostgreSqlAccountRepository($this->connection, new AccountPersistenceMapper);
        $store = new PostgreSqlAccountStatusWorkflowStore(
            $this->connection,
            $this->accounts,
            new AccountStatusWorkflowMapper,
        );
        $this->orchestrator = new DeterministicAccountStatusOrchestrator(
            $store,
            new AccountStatusWorkflow,
            new PostgreSqlAccountStatusOrchestrationTransaction($this->connection),
        );
    }

    public function test_complete_sequence_bootstraps_decides_and_appends_without_mutating_historical_version(): void
    {
        $account = $this->account();
        $this->accounts->add($account);

        $result = $this->orchestrator->transition($this->context());

        self::assertSame(AccountStatusOrchestrationStatus::Applied, $result->status);
        self::assertSame(AccountStatusState::Suspended, $result->state);
        self::assertSame(0, $this->accounts->find($account->id())?->version());
        self::assertSame(
            2,
            (int) $this->connection->query(
                "SELECT COUNT(*) FROM identity_access.account_status_lifecycle_transitions WHERE account_id = '{$account->id()->value}'",
            )->fetchColumn(),
        );
    }

    public function test_external_transaction_owns_rollback_for_the_complete_sequence(): void
    {
        $account = $this->account();
        $this->accounts->add($account);
        $this->connection->beginTransaction();

        self::assertSame(AccountStatusOrchestrationStatus::Applied, $this->orchestrator->transition($this->context())->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertSame(
            0,
            (int) $this->connection->query(
                "SELECT COUNT(*) FROM identity_access.account_status_lifecycle_transitions WHERE account_id = '{$account->id()->value}'",
            )->fetchColumn(),
        );
        self::assertSame(0, $this->accounts->find($account->id())?->version());
    }

    private function context(): AccountStatusContextV1
    {
        return new AccountStatusContextV1(
            $this->id(),
            AccountStatusState::Active,
            new AccountStatusVersion(0),
            new AccountStatusVersion(0),
            AccountStatusAction::Suspend,
            new AccountStatusActorId('operator-49e'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T10:00:00+00:00')),
            new AccountStatusIntentId('intent-49e'),
        );
    }

    private function account(): Account
    {
        $at = new DateTimeImmutable('2026-07-26T09:00:00+00:00');
        $account = Account::register(
            $this->id(),
            EmailAddress::fromString('orchestration@example.test'),
            PhoneNumber::fromString('+221770049002'),
            PersonName::fromString('Runtime Orchestration'),
            PasswordHash::fromString('$generic$v=1$salt-test$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $at->modify('+1 hour'),
            $at,
        );
        $account->releaseEvents();

        return $account;
    }

    private function id(): AccountId
    {
        return AccountId::fromString('49e00000-0000-4000-8000-000000000002');
    }
}
