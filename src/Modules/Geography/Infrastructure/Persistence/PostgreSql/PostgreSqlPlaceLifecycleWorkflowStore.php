<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceReadResult;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceWriteResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPlaceLifecycleWorkflowStore implements PlaceLifecycleWorkflowStore
{
    public function __construct(
        private PDO $connection,
        private PlaceLifecycleWorkflowMapper $mapper,
    ) {}

    public function initialize(
        PlaceId $placeId,
        PlaceLifecycleState $state,
        int $version,
    ): PlaceLifecyclePersistenceWriteResult {
        return $this->withinTransaction(function () use ($placeId, $state, $version): PlaceLifecyclePersistenceWriteResult {
            $this->lock([$placeId]);
            $parameters = $this->mapper->initial($placeId, $state, $version);
            $existing = $this->atVersion($placeId, $version);

            if ($existing !== false) {
                return hash_equals((string) $existing['entry_checksum'], (string) $parameters['checksum'])
                    ? PlaceLifecyclePersistenceWriteResult::AlreadyApplied
                    : PlaceLifecyclePersistenceWriteResult::StateConflict;
            }

            if ($this->current($placeId, true) !== false) {
                return PlaceLifecyclePersistenceWriteResult::SourceVersionConflict;
            }

            $this->insert($parameters);

            return PlaceLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function append(
        PlaceLifecycleTransition $transition,
        PlaceMergeContextV1 $context,
    ): PlaceLifecyclePersistenceWriteResult {
        return $this->withinTransaction(function () use ($transition, $context): PlaceLifecyclePersistenceWriteResult {
            $lockedPlaces = $transition->action === PlaceLifecycleAction::Merge
                ? [$context->sourceId, $context->targetId]
                : [$context->sourceId];
            $this->lock($lockedPlaces);
            $parameters = $this->mapper->transition($transition, $context);
            $resultVersion = $context->expectedSourceVersion->value + 1;
            $existing = $this->atVersion($context->sourceId, $resultVersion);

            if ($existing !== false) {
                return hash_equals((string) $existing['entry_checksum'], (string) $parameters['checksum'])
                    ? PlaceLifecyclePersistenceWriteResult::AlreadyApplied
                    : PlaceLifecyclePersistenceWriteResult::SourceVersionConflict;
            }

            $source = $this->current($context->sourceId, true);
            if ($source === false || (int) $source['version'] !== $context->expectedSourceVersion->value) {
                return PlaceLifecyclePersistenceWriteResult::SourceVersionConflict;
            }

            if ($transition->action === PlaceLifecycleAction::Merge) {
                $target = $this->current($context->targetId, true);
                if ($target === false || (int) $target['version'] !== $context->observedTargetVersion->value) {
                    return PlaceLifecyclePersistenceWriteResult::TargetVersionConflict;
                }
            }

            if ((string) $source['current_state'] !== $transition->from->value) {
                return PlaceLifecyclePersistenceWriteResult::StateConflict;
            }

            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return PlaceLifecyclePersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return PlaceLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function read(PlaceId $placeId): PlaceLifecyclePersistenceReadResult
    {
        $row = $this->current($placeId, false);
        if ($row === false) {
            return PlaceLifecyclePersistenceReadResult::missing($placeId);
        }

        try {
            return PlaceLifecyclePersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return PlaceLifecyclePersistenceReadResult::corrupted($placeId);
        }
    }

    /** @param callable(): PlaceLifecyclePersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): PlaceLifecyclePersistenceWriteResult
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

    /** @param list<PlaceId> $placeIds */
    private function lock(array $placeIds): void
    {
        $ids = array_values(array_unique(array_map(static fn (PlaceId $id): string => $id->value, $placeIds)));
        sort($ids, SORT_STRING);
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:place_id,0))');

        foreach ($ids as $id) {
            $statement->execute(['place_id' => $id]);
        }
    }

    /** @return array<string, mixed>|false */
    private function current(PlaceId $placeId, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE place_id=CAST(:place_id AS uuid) ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['place_id' => $placeId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|false */
    private function atVersion(PlaceId $placeId, int $version): array|false
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE place_id=CAST(:place_id AS uuid) AND version=:version');
        $statement->execute(['place_id' => $placeId->value, 'version' => $version]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT place_id::text,version,entry_kind,previous_state,current_state,action,target_id::text,target_version,target_state,source_type,target_type,source_country,target_country,actor_id::text,to_char(occurred_at AT TIME ZONE \'UTC\',\'YYYY-MM-DD"T"HH24:MI:SS.US"Z"\') AS occurred_at,intent_id::text,context_version,entry_checksum FROM geography.place_lifecycle_transitions';
    }

    /** @param array<string, int|string|null> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO geography.place_lifecycle_transitions(place_id,version,entry_kind,previous_state,current_state,action,target_id,target_version,target_state,source_type,target_type,source_country,target_country,actor_id,occurred_at,intent_id,context_version,entry_checksum) VALUES(CAST(:place_id AS uuid),:version,:entry_kind,:previous_state,:current_state,:action,CAST(:target_id AS uuid),:target_version,:target_state,:source_type,:target_type,:source_country,:target_country,CAST(:actor_id AS uuid),CAST(:occurred_at AS timestamptz),CAST(:intent_id AS uuid),:context_version,:checksum)',
        );
        $statement->execute($parameters);
    }
}
