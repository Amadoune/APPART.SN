<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\Contract\OwnerPersistenceStore;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;
use PDOException;
use Throwable;

/**
 * Transactional mechanism shared by the eight owners. Each concrete store fixes
 * its table, identity, and writable column allow-list; no caller controls SQL.
 */
abstract readonly class AbstractPostgreSqlOwnerPersistenceStore implements OwnerPersistenceStore
{
    /** @param list<string> $columns */
    public function __construct(
        protected PDO $connection,
        protected IdentityAccessCompletionPersistenceMapper $mapper,
        private string $table,
        private string $identityColumn,
        private array $columns,
        private string $intentColumn = 'last_intent_id',
        private string $checksumColumn = 'last_intent_checksum',
        private string $versionColumn = 'version',
        private bool $appendOnly = false,
    ) {}

    final public function read(string $identity): ?OwnerPersistenceState
    {
        $statement = $this->connection->prepare(
            "SELECT * FROM identity_access_completion.{$this->table} WHERE {$this->identityColumn}=CAST(:identity AS {$this->identityCast()})",
        );
        $statement->execute(['identity' => $identity]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->snapshot(
            $row,
            $this->identityColumn,
            $this->columns,
            $this->intentColumn,
            $this->checksumColumn,
            $this->versionColumn,
        );
    }

    final public function save(OwnerPersistenceState $state, int $expectedVersion): PersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $this->lock($state->identity);
            $current = $this->read($state->identity);
            $currentVersion = $current === null ? 0 : $current->version;
            if ($current !== null && $current->intentId === $state->intentId) {
                $result = hash_equals($current->intentChecksum, $state->intentChecksum)
                    ? PersistenceWriteResult::IdempotentReplay
                    : PersistenceWriteResult::PersistenceRejected;
            } elseif ($this->appendOnly && $current !== null) {
                $result = PersistenceWriteResult::IdentityConflict;
            } elseif ($currentVersion !== $expectedVersion || $state->version !== $expectedVersion + 1) {
                $result = PersistenceWriteResult::VersionConflict;
            } else {
                $this->write($state, $current === null);
                $result = PersistenceWriteResult::Applied;
            }
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollbackOwnedTransaction($owner);
            if ($error->getCode() === '23505') {
                return PersistenceWriteResult::IdentityConflict;
            }
            if ($error->getCode() === '23514' || $error->getCode() === '22P02') {
                return PersistenceWriteResult::PersistenceRejected;
            }
            throw $error;
        } catch (Throwable $error) {
            $this->rollbackOwnedTransaction($owner);
            throw $error;
        }
    }

    private function lock(string $identity): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:owner_key,0))');
        $statement->execute(['owner_key' => $this->table.':'.$identity]);
    }

    private function rollbackOwnedTransaction(bool $owner): void
    {
        if ($owner && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    private function write(OwnerPersistenceState $state, bool $insert): void
    {
        $parameters = ['identity' => $state->identity, 'version' => $state->version, 'intent' => $state->intentId, 'checksum' => $state->intentChecksum];
        foreach ($this->columns as $column) {
            $parameters[$column] = $state->values[$column] ?? null;
        }
        if ($insert) {
            $names = array_merge([$this->identityColumn, $this->versionColumn, $this->intentColumn, $this->checksumColumn], $this->columns);
            $binds = array_merge(['CAST(:identity AS '.$this->identityCast().')', ':version', 'CAST(:intent AS uuid)', ':checksum'], array_map(static fn (string $column): string => ':'.$column, $this->columns));
            $sql = "INSERT INTO identity_access_completion.{$this->table}(".implode(',', $names).') VALUES('.implode(',', $binds).')';
        } else {
            $sets = array_map(static fn (string $column): string => $column.'=:'.$column, $this->columns);
            $sets[] = $this->versionColumn.'=:version';
            $sets[] = $this->intentColumn.'=CAST(:intent AS uuid)';
            $sets[] = $this->checksumColumn.'=:checksum';
            $sql = "UPDATE identity_access_completion.{$this->table} SET ".implode(',', $sets)." WHERE {$this->identityColumn}=CAST(:identity AS {$this->identityCast()})";
        }
        $statement = $this->connection->prepare($sql);
        foreach ($parameters as $name => $value) {
            $type = match (true) {
                is_bool($value) => PDO::PARAM_BOOL,
                is_int($value) => PDO::PARAM_INT,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue(':'.$name, $value, $type);
        }
        $statement->execute();
    }

    private function identityCast(): string
    {
        return $this->identityColumn === 'attempt_key' ? 'varchar' : 'uuid';
    }
}
