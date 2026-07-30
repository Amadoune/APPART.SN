<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use PDO;

final readonly class PostgreSqlSessionStore extends AbstractPostgreSqlOwnerPersistenceStore
{
    public function __construct(PDO $connection, IdentityAccessCompletionPersistenceMapper $mapper)
    {
        parent::__construct($connection, $mapper, 'sessions', 'session_id', [
            'account_id', 'secret_hash', 'state', 'issued_at', 'expires_at', 'last_seen_at',
            'rotated_to', 'device_reference', 'issued_checkpoint',
        ]);
    }

    public function readInvalidationCheckpoint(string $accountId): ?OwnerPersistenceState
    {
        $statement = $this->connection->prepare(
            'SELECT account_id::text,checkpoint,version,last_intent_id::text,last_intent_checksum,updated_at::text
             FROM identity_access_completion.session_invalidation_checkpoints
             WHERE account_id=CAST(:account_id AS uuid)',
        );
        $statement->execute(['account_id' => $accountId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->snapshot($row, 'account_id', ['checkpoint', 'updated_at']);
    }

    public function advanceInvalidationCheckpoint(OwnerPersistenceState $state, int $expectedVersion): PersistenceWriteResult
    {
        $current = $this->readInvalidationCheckpoint($state->identity);
        $currentVersion = $current === null ? 0 : $current->version;
        if ($current !== null && $current->intentId === $state->intentId) {
            return hash_equals($current->intentChecksum, $state->intentChecksum)
                ? PersistenceWriteResult::IdempotentReplay
                : PersistenceWriteResult::PersistenceRejected;
        }
        if ($currentVersion !== $expectedVersion || $state->version !== $expectedVersion + 1) {
            return PersistenceWriteResult::VersionConflict;
        }
        $checkpoint = $state->values['checkpoint'] ?? null;
        $updatedAt = $state->values['updated_at'] ?? null;
        if (! is_int($checkpoint) || ! is_string($updatedAt)) {
            return PersistenceWriteResult::PersistenceRejected;
        }
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access_completion.session_invalidation_checkpoints
             (account_id,checkpoint,version,last_intent_id,last_intent_checksum,updated_at)
             VALUES(CAST(:account_id AS uuid),:checkpoint,:version,CAST(:intent AS uuid),:checksum,CAST(:updated_at AS timestamptz))
             ON CONFLICT (account_id) DO UPDATE SET
               checkpoint=EXCLUDED.checkpoint,version=EXCLUDED.version,last_intent_id=EXCLUDED.last_intent_id,
               last_intent_checksum=EXCLUDED.last_intent_checksum,updated_at=EXCLUDED.updated_at
             WHERE identity_access_completion.session_invalidation_checkpoints.version=:expected_version
               AND identity_access_completion.session_invalidation_checkpoints.checkpoint < EXCLUDED.checkpoint',
        );
        $statement->execute([
            'account_id' => $state->identity,
            'checkpoint' => $checkpoint,
            'version' => $state->version,
            'intent' => $state->intentId,
            'checksum' => $state->intentChecksum,
            'updated_at' => $updatedAt,
            'expected_version' => $expectedVersion,
        ]);

        return $statement->rowCount() === 1
            ? PersistenceWriteResult::Applied
            : PersistenceWriteResult::VersionConflict;
    }
}
