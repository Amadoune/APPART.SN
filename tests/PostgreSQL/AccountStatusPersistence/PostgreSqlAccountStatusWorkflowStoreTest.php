<?php

namespace Tests\PostgreSQL\AccountStatusPersistence;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\IdentityAccess\AccountTestData;

final class PostgreSqlAccountStatusWorkflowStoreTest extends TestCase
{
    private PDO $connection;

    private InMemoryHistoricalAccountRegistry $accounts;

    private PostgreSqlAccountStatusWorkflowStore $store;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->accounts = new InMemoryHistoricalAccountRegistry;
        $this->store = new PostgreSqlAccountStatusWorkflowStore(
            $this->connection,
            $this->accounts,
            new AccountStatusWorkflowMapper,
        );
    }

    public function test_absent_legacy_and_initialized_accounts_are_distinct(): void
    {
        self::assertSame(
            AccountStatusPersistenceReadStatus::AccountMissing,
            $this->store->read(AccountTestData::id())->status,
        );

        $this->accounts->put(AccountTestData::account());
        $legacy = $this->store->read(AccountTestData::id());
        self::assertSame(AccountStatusPersistenceReadStatus::LegacyUninitialized, $legacy->status);
        self::assertSame(AccountStatusState::Active, $legacy->legacyState);

        self::assertSame(AccountStatusPersistenceWriteResult::Applied, $this->store->bootstrap(AccountTestData::id()));
        self::assertSame(AccountStatusPersistenceWriteResult::Applied, $this->store->bootstrap(AccountTestData::id()));

        $found = $this->store->read(AccountTestData::id());
        self::assertSame(AccountStatusPersistenceReadStatus::Found, $found->status);
        self::assertNotNull($found->snapshot);
        self::assertSame(AccountStatusState::Active, $found->snapshot->state);
        self::assertSame(0, $found->snapshot->version->value);
        self::assertSame(1, $this->countRows(AccountTestData::id()));
        self::assertSame(0, $this->accounts->saveCalls);
    }

    public function test_bootstrap_preserves_a_suspended_legacy_state_at_lifecycle_version_zero(): void
    {
        $id = AccountTestData::id('000000000002');
        $this->accounts->put(AccountTestData::account('000000000002', true));

        self::assertSame(AccountStatusPersistenceWriteResult::Applied, $this->store->bootstrap($id));
        $found = $this->store->read($id);

        self::assertNotNull($found->snapshot);
        self::assertSame(AccountStatusState::Suspended, $found->snapshot->state);
        self::assertSame(0, $found->snapshot->version->value);
        self::assertSame(1, (int) $this->connection->query(
            "SELECT legacy_account_version FROM identity_access.account_status_lifecycle_transitions WHERE account_id='{$id->value}'",
        )->fetchColumn());
    }

    public function test_append_is_authoritative_and_detects_version_conflicts_without_saving_account(): void
    {
        $this->accounts->put(AccountTestData::account());
        self::assertSame(AccountStatusPersistenceWriteResult::Applied, $this->store->bootstrap(AccountTestData::id()));

        self::assertSame(
            AccountStatusPersistenceWriteResult::Applied,
            $this->store->append($this->transition(), $this->context()),
        );

        $found = $this->store->read(AccountTestData::id());
        self::assertNotNull($found->snapshot);
        self::assertSame(AccountStatusState::Suspended, $found->snapshot->state);
        self::assertSame(1, $found->snapshot->version->value);
        self::assertSame(2, $this->countRows(AccountTestData::id()));
        self::assertFalse($this->accounts->find(AccountTestData::id())?->isSuspended());
        self::assertSame(0, $this->accounts->saveCalls);

        self::assertSame(
            AccountStatusPersistenceWriteResult::VersionConflict,
            $this->store->append($this->transition(), $this->context('intent-2')),
        );
        self::assertSame(2, $this->countRows(AccountTestData::id()));
    }

    public function test_missing_account_and_uninitialized_lifecycle_are_closed_write_results(): void
    {
        self::assertSame(
            AccountStatusPersistenceWriteResult::AccountMissing,
            $this->store->bootstrap(AccountTestData::id()),
        );
        self::assertSame(
            AccountStatusPersistenceWriteResult::AccountMissing,
            $this->store->append($this->transition(), $this->context()),
        );

        $this->accounts->put(AccountTestData::account());
        self::assertSame(
            AccountStatusPersistenceWriteResult::PersistenceRejected,
            $this->store->append($this->transition(), $this->context()),
        );
    }

    public function test_corruption_and_invalid_transition_are_rejected_without_partial_write(): void
    {
        $this->accounts->put(AccountTestData::account());
        $this->store->bootstrap(AccountTestData::id());
        $this->connection->exec(
            "UPDATE identity_access.account_status_lifecycle_transitions SET entry_checksum='"
            .str_repeat('0', 64)."' WHERE account_id='".AccountTestData::id()->value."'",
        );

        self::assertSame(
            AccountStatusPersistenceReadStatus::PersistenceRejected,
            $this->store->read(AccountTestData::id())->status,
        );
        self::assertSame(
            AccountStatusPersistenceWriteResult::PersistenceRejected,
            $this->store->append($this->transition(), $this->context()),
        );
        self::assertSame(1, $this->countRows(AccountTestData::id()));
    }

    public function test_store_participates_in_an_outer_transaction(): void
    {
        $this->accounts->put(AccountTestData::account());
        $this->connection->beginTransaction();
        self::assertSame(AccountStatusPersistenceWriteResult::Applied, $this->store->bootstrap(AccountTestData::id()));
        $this->connection->rollBack();

        self::assertSame(0, $this->countRows(AccountTestData::id()));
        self::assertSame(AccountStatusPersistenceReadStatus::LegacyUninitialized, $this->store->read(AccountTestData::id())->status);
    }

    public function test_concurrent_bootstrap_is_atomic_and_converges_to_one_version_zero_entry(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'account-status-'.bin2hex(random_bytes(8));
        $processes = [];

        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'bootstrap-concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start account status bootstrap worker.');
            }
            $processes[] = [$process, $pipes];
        }

        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];

        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException($error);
            }
        }

        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['applied', 'applied'], $results);
        self::assertSame(1, $this->countRows(AccountTestData::id()));
    }

    public function test_migration_is_reversible_and_can_be_applied_again(): void
    {
        $root = dirname(__DIR__, 3)
            .'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/';
        $outboxRoot = dirname(__DIR__, 3)
            .'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents(
            $outboxRoot.'043_identity_access_outbox_owner.down.sql',
        ));
        $this->connection->exec((string) file_get_contents($root.'042_historical_account_persistence.down.sql'));
        $this->connection->exec((string) file_get_contents($root.'041_account_status_lifecycle_workflow.down.sql'));
        self::assertNull($this->connection->query(
            "SELECT to_regclass('identity_access.account_status_lifecycle_transitions')",
        )->fetchColumn());

        $this->connection->exec((string) file_get_contents($root.'041_account_status_lifecycle_workflow.sql'));
        $this->connection->exec((string) file_get_contents($root.'042_historical_account_persistence.sql'));
        $this->connection->exec((string) file_get_contents(
            $outboxRoot.'043_identity_access_outbox_owner.sql',
        ));
        self::assertSame(
            'identity_access.account_status_lifecycle_transitions',
            $this->connection->query(
                "SELECT to_regclass('identity_access.account_status_lifecycle_transitions')",
            )->fetchColumn(),
        );
    }

    private function transition(): AccountStatusTransition
    {
        return new AccountStatusTransition(
            AccountStatusState::Active,
            AccountStatusAction::Suspend,
            AccountStatusState::Suspended,
        );
    }

    private function context(string $intent = 'intent-1'): AccountStatusContextV1
    {
        return new AccountStatusContextV1(
            AccountTestData::id(),
            AccountStatusState::Active,
            new AccountStatusVersion(0),
            new AccountStatusVersion(0),
            AccountStatusAction::Suspend,
            new AccountStatusActorId('operator-1'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T10:00:00+00:00')),
            new AccountStatusIntentId($intent),
        );
    }

    private function countRows(AccountId $id): int
    {
        return (int) $this->connection->query(
            "SELECT COUNT(*) FROM identity_access.account_status_lifecycle_transitions WHERE account_id='{$id->value}'",
        )->fetchColumn();
    }
}

final class InMemoryHistoricalAccountRegistry implements AccountRegistry
{
    /** @var array<string, Account> */
    private array $accounts = [];

    public int $saveCalls = 0;

    public function put(Account $account): void
    {
        $this->accounts[$account->id()->value] = clone $account;
    }

    public function find(AccountId $id): ?Account
    {
        $account = $this->accounts[$id->value] ?? null;

        return $account === null ? null : clone $account;
    }

    public function add(Account $account): void
    {
        $this->put($account);
    }

    public function save(Account $account, int $expectedVersion): void
    {
        $this->saveCalls++;
        $this->put($account);
    }
}
