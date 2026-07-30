<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadResult;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlMediaItemLifecycleContextualTransitionRepository implements MediaItemLifecycleContextualTransitionStore
{
    public function __construct(
        private PDO $connection,
        private MediaItemLifecycleWorkflowStore $historical,
        private MediaItemLifecycleWorkflowMapper $workflowMapper,
        private MediaItemLifecycleContextMapper $contextMapper,
    ) {}

    public function read(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceReadResult
    {
        return $this->historical->read($mediaId);
    }

    public function append(MediaItemLifecycleContextualAppend $append): MediaItemLifecycleContextualWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $this->lock($append->mediaId);
            $current = $this->current($append->mediaId);
            $transition = $this->workflowMapper->transition($append->mediaId, $append->transition, $append->nextVersion());
            $context = $this->contextMapper->map($append);

            if ($current === false) {
                return $this->finish($owner, MediaItemLifecycleContextualWriteResult::VersionConflict);
            }
            if ((int) $current['version'] === $append->nextVersion()) {
                if (! hash_equals((string) $current['transition_checksum'], $transition['checksum'])) {
                    return $this->finish($owner, MediaItemLifecycleContextualWriteResult::StateConflict);
                }
                $stored = $this->context($append->mediaId, $append->nextVersion());
                if ($stored === false) {
                    return $this->finish($owner, MediaItemLifecycleContextualWriteResult::Corrupted);
                }

                return $this->finish(
                    $owner,
                    hash_equals((string) $stored['context_checksum'], (string) $context['context_checksum'])
                        ? MediaItemLifecycleContextualWriteResult::AlreadyApplied
                        : MediaItemLifecycleContextualWriteResult::ContextDivergence,
                );
            }
            if ((int) $current['version'] !== $append->context->expectedVersion->value) {
                return $this->finish($owner, MediaItemLifecycleContextualWriteResult::VersionConflict);
            }
            if ((string) $current['current_state'] !== $append->transition->from->value) {
                return $this->finish($owner, MediaItemLifecycleContextualWriteResult::StateConflict);
            }

            try {
                $this->insertTransition($transition);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return $this->finish($owner, MediaItemLifecycleContextualWriteResult::TransitionRejected);
                }
                throw $error;
            }
            $this->insertContext($context);

            return $this->finish($owner, MediaItemLifecycleContextualWriteResult::Applied);
        } catch (Throwable $error) {
            if ($owner && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function finish(bool $owner, MediaItemLifecycleContextualWriteResult $result): MediaItemLifecycleContextualWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }

    private function lock(MediaItemLifecycleId $mediaId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:media_id,0))');
        $statement->execute(['media_id' => $mediaId->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(MediaItemLifecycleId $mediaId): array|false
    {
        $statement = $this->connection->prepare('SELECT version,current_state,transition_checksum FROM media.media_item_lifecycle_transitions WHERE media_id=:media_id ORDER BY version DESC LIMIT 1 FOR UPDATE');
        $statement->execute(['media_id' => $mediaId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|false */
    private function context(MediaItemLifecycleId $mediaId, int $version): array|false
    {
        $statement = $this->connection->prepare('SELECT context_checksum FROM media.media_item_lifecycle_transition_contexts WHERE media_id=:media_id AND version=:version');
        $statement->execute(['media_id' => $mediaId->value, 'version' => $version]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $parameters */
    private function insertTransition(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO media.media_item_lifecycle_transitions(media_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:media_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }

    /** @param array<string, mixed> $parameters */
    private function insertContext(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO media.media_item_lifecycle_transition_contexts(media_id,version,contract_version,collection_id,collection_version,actor_id,occurred_at,primary_disposition,replacement_media_id,context_checksum) VALUES(CAST(:media_id AS uuid),:version,:contract_version,CAST(:collection_id AS uuid),:collection_version,CAST(:actor_id AS uuid),CAST(:occurred_at AS timestamptz),:primary_disposition,CAST(:replacement_media_id AS uuid),:context_checksum)');
        $statement->execute($parameters);
    }
}
