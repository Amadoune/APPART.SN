<?php

namespace Tests\PostgreSQL\IdentityAccessCompletion;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicOperation;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityAccessAtomicTransaction;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlIdentityAccessAtomicTransactionTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function two_owner_writes_and_intent_commit_together_and_replay_once(): void
    {
        $transaction = new PostgreSqlIdentityAccessAtomicTransaction($this->connection);
        $command = $this->command(IdentityAccessAtomicOperation::Authentication);
        $executions = 0;
        $work = function () use (&$executions): IdentityAccessAtomicWorkResult {
            $executions++;
            $this->insertAttempt();
            $this->insertRecovery();

            return IdentityAccessAtomicWorkResult::Applied;
        };

        self::assertSame(IdentityAccessOrchestrationStatus::Applied, $transaction->execute($command, $work)->status);
        self::assertSame(IdentityAccessOrchestrationStatus::IdempotentReplay, $transaction->execute($command, $work)->status);
        self::assertSame(1, $executions);
        self::assertSame(1, $this->tableCount('authentication_attempts'));
        self::assertSame(1, $this->tableCount('recovery_challenges'));
        self::assertSame(1, $this->tableCount('atomic_operation_intents'));
    }

    #[Test]
    public function divergent_replay_is_closed_without_executing_work(): void
    {
        $transaction = new PostgreSqlIdentityAccessAtomicTransaction($this->connection);
        $command = $this->command(IdentityAccessAtomicOperation::Session);
        self::assertSame(
            IdentityAccessOrchestrationStatus::Applied,
            $transaction->execute($command, static fn (): IdentityAccessAtomicWorkResult => IdentityAccessAtomicWorkResult::Applied)->status,
        );
        $divergent = new IdentityAccessAtomicCommand(
            $command->intentId,
            str_repeat('b', 64),
            $command->accountId,
            $command->operation,
            $command->occurredAt,
        );

        self::assertSame(
            IdentityAccessOrchestrationStatus::ReplayConflict,
            $transaction->execute($divergent, static fn (): IdentityAccessAtomicWorkResult => throw new RuntimeException)->status,
        );
    }

    #[Test]
    public function exception_rolls_back_every_owner_and_the_intent(): void
    {
        $result = (new PostgreSqlIdentityAccessAtomicTransaction($this->connection))->execute(
            $this->command(IdentityAccessAtomicOperation::PasswordRecovery),
            function (): IdentityAccessAtomicWorkResult {
                $this->insertAttempt();
                throw new RuntimeException('fail after first owner');
            },
        );

        self::assertSame(IdentityAccessOrchestrationStatus::RolledBack, $result->status);
        self::assertSame(0, $this->tableCount('authentication_attempts'));
        self::assertSame(0, $this->tableCount('atomic_operation_intents'));
    }

    #[Test]
    public function concurrent_identical_operations_converge_to_one_atomic_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'iam-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'atomic-concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start IAM atomic operation worker.');
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
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['Applied', 'IdempotentReplay'], $results);
        self::assertSame(1, $this->tableCount('authentication_attempts'));
        self::assertSame(1, $this->tableCount('atomic_operation_intents'));
    }

    private function command(IdentityAccessAtomicOperation $operation): IdentityAccessAtomicCommand
    {
        return new IdentityAccessAtomicCommand(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            str_repeat('a', 64),
            AccountId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
            $operation,
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
        );
    }

    private function insertAttempt(): void
    {
        $this->connection->exec(
            "INSERT INTO identity_access_completion.authentication_attempts
             (attempt_key,policy_version,failure_count,version,last_intent_id,last_intent_checksum,last_outcome,updated_at)
             VALUES('atomic-key','v1',1,1,'cccccccc-cccc-4ccc-8ccc-cccccccccccc','".str_repeat('c', 64)."','Rejected',now())",
        );
    }

    private function insertRecovery(): void
    {
        $this->connection->exec(
            "INSERT INTO identity_access_completion.recovery_challenges
             (challenge_id,account_id,challenge_hash,state,version,issued_at,expires_at,policy_version,last_intent_id,last_intent_checksum)
             VALUES('dddddddd-dddd-4ddd-8ddd-dddddddddddd','bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb','hash','Issued',1,now(),now()+interval '1 hour','v1','eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee','".str_repeat('d', 64)."')",
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query(
            "SELECT count(*) FROM identity_access_completion.{$table}",
        )->fetchColumn();
    }
}
