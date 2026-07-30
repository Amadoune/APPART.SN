<?php

namespace Tests\PostgreSQL\HistoricalAccountPersistence;

use Appart\Modules\IdentityAccess\Domain\Exception\ConcurrentAccountModification;
use Appart\Modules\IdentityAccess\Domain\Exception\DuplicateAccountIdentity;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\CorruptedHistoricalAccountPersistence;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAccountRepositoryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAccountRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->repository = new PostgreSqlAccountRepository($this->connection, new AccountPersistenceMapper);
    }

    public function test_add_and_find_round_trip_the_complete_aggregate(): void
    {
        $account = $this->persistRichAccount();
        $before = (new AccountPersistenceMapper)->snapshot($account);

        $restored = $this->repository->find($account->id());
        self::assertNotNull($restored);
        $after = (new AccountPersistenceMapper)->snapshot($restored);

        self::assertSame($before->accountId->value, $after->accountId->value);
        self::assertSame($before->email->value, $after->email->value);
        self::assertSame($before->phone->value, $after->phone->value);
        self::assertSame($before->lastChangedAt->format('c'), $after->lastChangedAt->format('c'));
        self::assertSame($before->historicalVersion, $after->historicalVersion);
        self::assertSame($before->historicalSuspended, $after->historicalSuspended);
        self::assertSame(2, count($after->roleAssignments));
        self::assertSame(2, count($after->consents));
        self::assertSame([], $restored->releaseEvents());
        self::assertSame(1, $this->rowCountFor('accounts'));
        self::assertSame(1, $this->rowCountFor('account_credentials'));
        self::assertSame(2, $this->rowCountFor('account_verifications'));
        self::assertSame(2, $this->rowCountFor('account_role_assignments'));
        self::assertSame(2, $this->rowCountFor('account_consents'));
    }

    public function test_find_returns_null_only_for_an_absent_account(): void
    {
        self::assertNull($this->repository->find($this->id()));
    }

    public function test_add_translates_account_id_email_and_phone_uniqueness(): void
    {
        $this->repository->add($this->account());

        foreach ([
            [$this->account(), 'identifier'],
            [$this->account('000000000002', 'account@example.test', '+221770000002'), 'email'],
            [$this->account('000000000003', 'third@example.test', '+221770000001'), 'phone'],
        ] as [$duplicate, $field]) {
            try {
                $this->repository->add($duplicate);
                self::fail('Duplicate '.$field.' must be rejected.');
            } catch (DuplicateAccountIdentity $error) {
                self::assertStringContainsString($field, $error->getMessage());
            }
        }
        self::assertSame(1, $this->rowCountFor('accounts'));
    }

    public function test_save_uses_atomic_optimistic_version_update_and_preserves_children(): void
    {
        $account = $this->account();
        $this->repository->add($account);
        $loaded = $this->repository->find($account->id());
        self::assertNotNull($loaded);
        $expectedVersion = $loaded->version();
        $loaded->grantRole(RoleId::fromString('manager'), new DateTimeImmutable('2026-07-26T09:10:00+00:00'));
        $this->repository->save($loaded, $expectedVersion);

        $restored = $this->repository->find($account->id());
        self::assertNotNull($restored);
        self::assertSame($expectedVersion + 1, $restored->version());
        self::assertTrue($restored->hasRole(RoleId::fromString('manager')));
        self::assertSame(1, $this->rowCountFor('account_role_assignments'));

        $this->expectException(ConcurrentAccountModification::class);
        $this->repository->save($loaded, $expectedVersion);
    }

    public function test_repository_joins_an_outer_transaction_without_committing_it(): void
    {
        $this->connection->beginTransaction();
        $this->repository->add($this->account());
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertNull($this->repository->find($this->id()));
    }

    public function test_child_failure_rolls_back_the_complete_add(): void
    {
        $this->connection->exec(
            "CREATE OR REPLACE FUNCTION identity_access.reject_account_credential() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'forced rollback'; END $$",
        );
        $this->connection->exec(
            'CREATE TRIGGER reject_account_credential BEFORE INSERT ON identity_access.account_credentials FOR EACH ROW EXECUTE FUNCTION identity_access.reject_account_credential()',
        );

        try {
            $this->repository->add($this->account());
            self::fail('The forced child failure must reject add.');
        } catch (CorruptedHistoricalAccountPersistence) {
            self::assertSame(0, $this->rowCountFor('accounts'));
        } finally {
            $this->connection->exec('DROP TRIGGER IF EXISTS reject_account_credential ON identity_access.account_credentials');
            $this->connection->exec('DROP FUNCTION IF EXISTS identity_access.reject_account_credential()');
        }
    }

    public function test_corruption_is_never_reported_as_missing(): void
    {
        $account = $this->account();
        $this->repository->add($account);
        $this->connection->exec('DELETE FROM identity_access.account_credentials');

        $this->expectException(CorruptedHistoricalAccountPersistence::class);
        $this->repository->find($account->id());
    }

    public function test_migration_is_reversible_and_does_not_modify_journal_041(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'042_historical_account_persistence.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('identity_access.accounts')")->fetchColumn());
        self::assertSame(
            'identity_access.account_status_lifecycle_transitions',
            $this->connection->query("SELECT to_regclass('identity_access.account_status_lifecycle_transitions')")->fetchColumn(),
        );

        $this->connection->exec((string) file_get_contents($root.'042_historical_account_persistence.sql'));
        self::assertSame(
            'identity_access.accounts',
            $this->connection->query("SELECT to_regclass('identity_access.accounts')")->fetchColumn(),
        );
    }

    public function test_two_processes_saving_the_same_version_produce_one_conflict(): void
    {
        $this->repository->add($this->account());
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'historical-account-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'save-concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start historical Account worker.');
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

        sort($results);
        self::assertSame(['conflict', 'saved'], $results);
        self::assertSame(1, $this->repository->find($this->id())?->version());
    }

    private function persistRichAccount(): Account
    {
        $account = $this->account();
        $this->repository->add($account);
        $base = new DateTimeImmutable('2026-07-26T09:00:00+00:00');
        foreach ([
            static fn (Account $candidate) => $candidate->grantRole(RoleId::fromString('manager'), $base->modify('+1 minute')),
            static fn (Account $candidate) => $candidate->revokeRole(RoleId::fromString('manager'), $base->modify('+2 minutes')),
            static fn (Account $candidate) => $candidate->grantConsent(ConsentPurpose::fromString('marketing'), $base->modify('+3 minutes')),
            static fn (Account $candidate) => $candidate->withdrawConsent(ConsentPurpose::fromString('marketing'), $base->modify('+4 minutes')),
            static fn (Account $candidate) => $candidate->grantRole(RoleId::fromString('manager'), $base->modify('+5 minutes')),
            static fn (Account $candidate) => $candidate->grantConsent(ConsentPurpose::fromString('marketing'), $base->modify('+6 minutes')),
        ] as $mutation) {
            $expected = $account->version();
            $mutation($account);
            $this->repository->save($account, $expected);
        }
        $account->releaseEvents();

        return $account;
    }

    private function account(
        string $suffix = '000000000001',
        string $email = 'account@example.test',
        string $phone = '+221770000001',
    ): Account {
        $at = new DateTimeImmutable('2026-07-26T09:00:00+00:00');
        $account = Account::register(
            $this->id($suffix),
            EmailAddress::fromString($email),
            PhoneNumber::fromString($phone),
            PersonName::fromString('Historical Account'),
            PasswordHash::fromString('$generic$v=1$salt-test$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $at->modify('+1 hour'),
            $at,
        );
        $account->releaseEvents();

        return $account;
    }

    private function id(string $suffix = '000000000001'): AccountId
    {
        return AccountId::fromString('49000000-0000-4000-8000-'.$suffix);
    }

    private function rowCountFor(string $table): int
    {
        return (int) $this->connection->query("SELECT COUNT(*) FROM identity_access.{$table}")->fetchColumn();
    }
}
