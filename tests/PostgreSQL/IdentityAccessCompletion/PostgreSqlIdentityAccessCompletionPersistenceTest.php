<?php

namespace Tests\PostgreSQL\IdentityAccessCompletion;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountClosureStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthenticationAttemptStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityClaimStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlPasswordRecoveryStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlPendingContactChangeStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlProfileRevisionStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlSessionStore;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlUserProfileStore;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlIdentityAccessCompletionPersistenceTest extends TestCase
{
    private static PDO $connection;

    public static function setUpBeforeClass(): void
    {
        self::$connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate(self::$connection);
    }

    protected function setUp(): void
    {
        PostgreSqlTestEnvironment::reset(self::$connection);
    }

    #[Test]
    public function optimistic_locking_and_deterministic_idempotence_are_enforced(): void
    {
        $store = new PostgreSqlAuthenticationAttemptStore(self::$connection, new IdentityAccessCompletionPersistenceMapper);
        $first = $this->attempt(1, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', str_repeat('a', 64), 1);

        self::assertSame(PersistenceWriteResult::Applied, $store->save($first, 0));
        self::assertSame(PersistenceWriteResult::IdempotentReplay, $store->save($first, 0));
        self::assertSame(
            PersistenceWriteResult::PersistenceRejected,
            $store->save($this->attempt(1, $first->intentId, str_repeat('b', 64), 1), 0),
        );
        self::assertSame(
            PersistenceWriteResult::VersionConflict,
            $store->save($this->attempt(3, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', str_repeat('b', 64), 2), 1),
        );
        self::assertSame(1, $store->read('login-key')?->version);
    }

    #[Test]
    public function an_outer_transaction_can_roll_back_a_complete_owner_write(): void
    {
        $store = new PostgreSqlUserProfileStore(self::$connection, new IdentityAccessCompletionPersistenceMapper);
        $accountId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        self::$connection->beginTransaction();
        self::assertSame(PersistenceWriteResult::Applied, $store->save(new OwnerPersistenceState(
            $accountId,
            1,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            str_repeat('c', 64),
            [
                'display_name_ciphertext' => 'ciphertext',
                'email_ciphertext' => null,
                'email_fingerprint' => null,
                'phone_ciphertext' => null,
                'phone_fingerprint' => null,
                'normalization_version' => 'v1',
                'enrolled_at' => '2026-01-01T00:00:00Z',
                'updated_at' => '2026-01-01T00:00:00Z',
            ],
        ), 0));
        self::$connection->rollBack();

        self::assertNull($store->read($accountId));
    }

    #[Test]
    public function session_invalidation_checkpoint_is_strictly_monotonic(): void
    {
        $store = new PostgreSqlSessionStore(self::$connection, new IdentityAccessCompletionPersistenceMapper);
        $accountId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $first = new OwnerPersistenceState(
            $accountId,
            1,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            str_repeat('d', 64),
            ['checkpoint' => 1, 'updated_at' => '2026-01-01T00:00:00Z'],
        );
        self::assertSame(PersistenceWriteResult::Applied, $store->advanceInvalidationCheckpoint($first, 0));
        self::assertSame(PersistenceWriteResult::IdempotentReplay, $store->advanceInvalidationCheckpoint($first, 0));
        $stale = new OwnerPersistenceState(
            $accountId,
            2,
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            str_repeat('e', 64),
            ['checkpoint' => 1, 'updated_at' => '2026-01-01T00:01:00Z'],
        );
        self::assertSame(PersistenceWriteResult::VersionConflict, $store->advanceInvalidationCheckpoint($stale, 1));
    }

    #[Test]
    public function every_remaining_owner_has_an_independent_round_trip(): void
    {
        $mapper = new IdentityAccessCompletionPersistenceMapper;
        $account = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $intent = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
        $checksum = str_repeat('f', 64);
        $at = '2026-01-01T00:00:00Z';
        $cases = [
            [new PostgreSqlPasswordRecoveryStore(self::$connection, $mapper), new OwnerPersistenceState(
                '10000000-0000-4000-8000-000000000001', 1, $intent, $checksum,
                ['account_id' => $account, 'challenge_hash' => 'hash', 'state' => 'Issued', 'issued_at' => $at, 'expires_at' => '2026-01-01T01:00:00Z', 'consumed_at' => null, 'policy_version' => 'v1'],
            )],
            [new PostgreSqlIdentityClaimStore(self::$connection, $mapper), new OwnerPersistenceState(
                '10000000-0000-4000-8000-000000000002', 1, $intent, $checksum,
                ['account_id' => $account, 'claim_type' => 'Email', 'claim_ciphertext' => 'cipher', 'claim_fingerprint' => str_repeat('1', 64), 'normalization_version' => 'v1', 'state' => 'Reserved', 'reserved_at' => $at, 'activated_at' => null, 'ended_at' => null, 'contact_change_id' => null],
            )],
            [new PostgreSqlPendingContactChangeStore(self::$connection, $mapper), new OwnerPersistenceState(
                '10000000-0000-4000-8000-000000000003', 1, $intent, $checksum,
                ['account_id' => $account, 'contact_type' => 'Email', 'target_ciphertext' => 'cipher', 'target_fingerprint' => str_repeat('2', 64), 'challenge_hash' => 'hash', 'claim_id' => '10000000-0000-4000-8000-000000000002', 'state' => 'Pending', 'requested_at' => $at, 'expires_at' => '2026-01-01T01:00:00Z', 'verified_at' => null, 'activated_at' => null, 'fresh_auth_evidence' => 'evidence', 'policy_version' => 'v1', 'normalization_version' => 'v1'],
            )],
            [new PostgreSqlProfileRevisionStore(self::$connection, $mapper), new OwnerPersistenceState(
                '10000000-0000-4000-8000-000000000004', 1, $intent, $checksum,
                ['account_id' => $account, 'revision_type' => 'ProfileEnrolled', 'actor_id' => $account, 'occurred_at' => $at, 'source_change_id' => null, 'old_value_ciphertext' => null, 'new_value_ciphertext' => 'cipher', 'policy_version' => 'v1', 'normalization_version' => 'v1'],
            )],
            [new PostgreSqlAccountClosureStore(self::$connection, $mapper), new OwnerPersistenceState(
                $account, 1, $intent, $checksum,
                ['state' => 'Open', 'requested_at' => null, 'closed_at' => null, 'reopened_at' => null, 'actor_id' => $account, 'reason_category' => 'baseline', 'policy_version' => 'v1', 'cooling_off_until' => null, 'retention_class' => 'standard', 'legal_hold' => false, 'session_checkpoint' => 0, 'updated_at' => $at],
            )],
        ];

        foreach ($cases as [$store, $snapshot]) {
            self::assertSame(PersistenceWriteResult::Applied, $store->save($snapshot, 0), $store::class);
            self::assertSame($snapshot->identity, $store->read($snapshot->identity)?->identity);
        }
    }

    #[Test]
    public function two_processes_converge_on_one_idempotent_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-iam-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start IAM persistence worker.');
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
                throw new RuntimeException('IAM persistence worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['Applied', 'IdempotentReplay'], $results);
        self::assertSame(1, (int) self::$connection->query(
            "SELECT count(*) FROM identity_access_completion.authentication_attempts WHERE attempt_key='concurrent-login-key'",
        )->fetchColumn());
    }

    #[Test]
    public function owner_migrations_expose_no_cross_domain_foreign_key_and_are_locally_reversible(): void
    {
        $foreignKeys = (int) self::$connection->query(
            "SELECT count(*) FROM pg_constraint c
             JOIN pg_namespace n ON n.oid=c.connamespace
             WHERE n.nspname='identity_access_completion' AND c.contype='f'",
        )->fetchColumn();
        self::assertSame(0, $foreignKeys);

        $root = dirname(__DIR__, 3).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::$connection->exec((string) file_get_contents($root.'051_account_closures.down.sql'));
        self::assertNull(self::$connection->query(
            "SELECT to_regclass('identity_access_completion.account_closures')",
        )->fetchColumn());
        self::assertSame(
            'identity_access_completion.user_profiles',
            self::$connection->query("SELECT to_regclass('identity_access_completion.user_profiles')")->fetchColumn(),
        );
        self::$connection->exec((string) file_get_contents($root.'051_account_closures.sql'));
    }

    private function attempt(int $version, string $intent, string $checksum, int $failureCount): OwnerPersistenceState
    {
        return new OwnerPersistenceState('login-key', $version, $intent, $checksum, [
            'policy_version' => 'v1',
            'failure_count' => $failureCount,
            'window_started_at' => '2026-01-01T00:00:00Z',
            'locked_until' => null,
            'last_outcome' => 'Rejected',
            'updated_at' => '2026-01-01T00:00:00Z',
        ]);
    }
}
