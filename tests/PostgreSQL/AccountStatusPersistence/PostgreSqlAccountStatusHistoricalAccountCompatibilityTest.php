<?php

namespace Tests\PostgreSQL\AccountStatusPersistence;

use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadStatus;
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
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAccountStatusHistoricalAccountCompatibilityTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAccountRepository $accounts;

    private PostgreSqlAccountStatusWorkflowStore $statuses;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->accounts = new PostgreSqlAccountRepository($this->connection, new AccountPersistenceMapper);
        $this->statuses = new PostgreSqlAccountStatusWorkflowStore(
            $this->connection,
            $this->accounts,
            new AccountStatusWorkflowMapper,
        );
    }

    public function test_bootstrap_reads_the_certified_historical_source_and_keeps_versions_separate(): void
    {
        $account = $this->account();
        $this->accounts->add($account);

        self::assertSame('applied', $this->statuses->bootstrap($account->id())->value);
        $status = $this->statuses->read($account->id());

        self::assertSame(AccountStatusPersistenceReadStatus::Found, $status->status);
        self::assertNotNull($status->snapshot);
        self::assertSame(0, $status->snapshot->version->value);
        self::assertSame(0, $this->accounts->find($account->id())?->version());
    }

    public function test_one_external_transaction_rolls_back_historical_account_and_041_bootstrap(): void
    {
        $account = $this->account();
        $this->connection->beginTransaction();
        $this->accounts->add($account);
        self::assertSame('applied', $this->statuses->bootstrap($account->id())->value);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertNull($this->accounts->find($account->id()));
        self::assertSame(
            0,
            (int) $this->connection->query(
                "SELECT COUNT(*) FROM identity_access.account_status_lifecycle_transitions WHERE account_id = '{$account->id()->value}'",
            )->fetchColumn(),
        );
    }

    private function account(): Account
    {
        $at = new DateTimeImmutable('2026-07-26T09:00:00+00:00');
        $account = Account::register(
            AccountId::fromString('49c00000-0000-4000-8000-000000000001'),
            EmailAddress::fromString('recertification@example.test'),
            PhoneNumber::fromString('+221770049001'),
            PersonName::fromString('Targeted Recertification'),
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
