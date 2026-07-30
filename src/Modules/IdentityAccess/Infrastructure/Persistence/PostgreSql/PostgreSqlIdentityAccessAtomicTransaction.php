<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract\IdentityAccessAtomicTransaction;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationStatus;
use PDO;
use Throwable;

final readonly class PostgreSqlIdentityAccessAtomicTransaction implements IdentityAccessAtomicTransaction
{
    public function __construct(private PDO $connection) {}

    public function execute(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'iam_atomic_operation';
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec("SAVEPOINT {$savepoint}");
        }

        try {
            $this->lock($command);
            $existing = $this->existing($command->intentId);
            if ($existing !== false) {
                $result = $this->replay($command, $existing);
                $this->finish($owner, $savepoint);

                return $result;
            }

            $workResult = $work();
            $this->record($command, $workResult);
            $this->finish($owner, $savepoint);

            return new IdentityAccessOrchestrationResult(
                $command->operation,
                $workResult === IdentityAccessAtomicWorkResult::Applied
                    ? IdentityAccessOrchestrationStatus::Applied
                    : IdentityAccessOrchestrationStatus::Rejected,
            );
        } catch (Throwable) {
            $this->rollback($owner, $savepoint);

            return new IdentityAccessOrchestrationResult(
                $command->operation,
                IdentityAccessOrchestrationStatus::RolledBack,
            );
        }
    }

    private function lock(IdentityAccessAtomicCommand $command): void
    {
        $statement = $this->connection->prepare(
            'SELECT pg_advisory_xact_lock(hashtextextended(:lock_key,0))',
        );
        $statement->execute(['lock_key' => $command->accountId->value.':'.$command->operation->value]);
    }

    /** @return array<string, mixed>|false */
    private function existing(string $intentId): array|false
    {
        $statement = $this->connection->prepare(
            'SELECT account_id,operation,intent_checksum,outcome
             FROM identity_access_completion.atomic_operation_intents
             WHERE intent_id=CAST(:intent_id AS uuid)',
        );
        $statement->execute(['intent_id' => $intentId]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $existing */
    private function replay(IdentityAccessAtomicCommand $command, array $existing): IdentityAccessOrchestrationResult
    {
        $compatible = ($existing['account_id'] ?? null) === $command->accountId->value
            && ($existing['operation'] ?? null) === $command->operation->value
            && is_string($existing['intent_checksum'] ?? null)
            && hash_equals($existing['intent_checksum'], $command->intentChecksum);

        return new IdentityAccessOrchestrationResult(
            $command->operation,
            $compatible
                ? IdentityAccessOrchestrationStatus::IdempotentReplay
                : IdentityAccessOrchestrationStatus::ReplayConflict,
        );
    }

    private function record(IdentityAccessAtomicCommand $command, IdentityAccessAtomicWorkResult $outcome): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access_completion.atomic_operation_intents
             (intent_id,account_id,operation,intent_checksum,outcome,completed_at)
             VALUES(CAST(:intent_id AS uuid),CAST(:account_id AS uuid),:operation,:checksum,:outcome,:completed_at)',
        );
        $statement->execute([
            'intent_id' => $command->intentId,
            'account_id' => $command->accountId->value,
            'operation' => $command->operation->value,
            'checksum' => $command->intentChecksum,
            'outcome' => $outcome->value,
            'completed_at' => $command->occurredAt->format('Y-m-d\TH:i:s.uP'),
        ]);
    }

    private function finish(bool $owner, string $savepoint): void
    {
        if ($owner) {
            $this->connection->commit();
        } else {
            $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
        }
    }

    private function rollback(bool $owner, string $savepoint): void
    {
        if ($owner && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        } elseif ($this->connection->inTransaction()) {
            $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
        }
    }
}
