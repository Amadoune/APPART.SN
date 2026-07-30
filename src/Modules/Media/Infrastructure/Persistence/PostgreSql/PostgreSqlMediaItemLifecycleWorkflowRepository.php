<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceWriteResult;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlMediaItemLifecycleWorkflowRepository implements MediaItemLifecycleWorkflowStore
{
    public function __construct(private PDO $connection, private MediaItemLifecycleWorkflowMapper $mapper) {}

    public function initialize(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($mediaId): MediaItemLifecyclePersistenceWriteResult {
            $this->lock($mediaId);
            $current = $this->current($mediaId, true);
            $parameters = $this->mapper->initial($mediaId);
            if ($current !== false) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum']) ? MediaItemLifecyclePersistenceWriteResult::AlreadyApplied : MediaItemLifecyclePersistenceWriteResult::StateConflict;
            }
            $this->insert($parameters);

            return MediaItemLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function append(MediaItemLifecycleId $mediaId, MediaItemLifecycleTransition $transition, int $version): MediaItemLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($mediaId, $transition, $version): MediaItemLifecyclePersistenceWriteResult {
            $this->lock($mediaId);
            $current = $this->current($mediaId, true);
            if ($current === false) {
                return MediaItemLifecyclePersistenceWriteResult::RejectedVersion;
            }
            $parameters = $this->mapper->transition($mediaId, $transition, $version);
            if ($version === (int) $current['version']) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum']) ? MediaItemLifecyclePersistenceWriteResult::AlreadyApplied : MediaItemLifecyclePersistenceWriteResult::StateConflict;
            }
            if ($version !== (int) $current['version'] + 1) {
                return MediaItemLifecyclePersistenceWriteResult::RejectedVersion;
            }
            if ((string) $current['current_state'] !== $transition->from->value) {
                return MediaItemLifecyclePersistenceWriteResult::StateConflict;
            }
            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return MediaItemLifecyclePersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return MediaItemLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function read(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceReadResult
    {
        $row = $this->current($mediaId, false);
        if ($row === false) {
            return MediaItemLifecyclePersistenceReadResult::missing($mediaId);
        }
        try {
            return MediaItemLifecyclePersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return MediaItemLifecyclePersistenceReadResult::corrupted($mediaId);
        }
    }

    /** @param callable():MediaItemLifecyclePersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): MediaItemLifecyclePersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function lock(MediaItemLifecycleId $mediaId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:media_id,0))');
        $statement->execute(['media_id' => $mediaId->value]);
    }

    /** @return array<string,mixed>|false */
    private function current(MediaItemLifecycleId $mediaId, bool $forUpdate): array|false
    {
        $sql = 'SELECT media_id::text,version,previous_state,current_state,action,transition_checksum FROM media.media_item_lifecycle_transitions WHERE media_id=:media_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['media_id' => $mediaId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string,mixed> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO media.media_item_lifecycle_transitions(media_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:media_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }
}
