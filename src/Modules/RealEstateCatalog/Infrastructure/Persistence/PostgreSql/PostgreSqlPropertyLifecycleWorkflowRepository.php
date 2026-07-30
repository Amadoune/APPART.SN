<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceReadResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyLifecycleWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPropertyLifecycleWorkflowRepository implements PropertyLifecycleWorkflowStore
{
    public function __construct(private PDO $connection, private PropertyLifecycleWorkflowMapper $mapper) {}

    public function initialize(PropertyId $propertyId, PropertyLifecycleState $state): PropertyLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($propertyId, $state): PropertyLifecyclePersistenceWriteResult {
            $this->lock($propertyId);
            $current = $this->current($propertyId, true);
            $parameters = $this->mapper->initial($propertyId, $state);
            if ($current !== false) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? PropertyLifecyclePersistenceWriteResult::AlreadyApplied
                    : PropertyLifecyclePersistenceWriteResult::StateConflict;
            }
            $this->insert($parameters);

            return PropertyLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function append(PropertyId $propertyId, PropertyLifecycleTransition $transition, int $version): PropertyLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($propertyId, $transition, $version): PropertyLifecyclePersistenceWriteResult {
            $this->lock($propertyId);
            $current = $this->current($propertyId, true);
            if ($current === false) {
                return PropertyLifecyclePersistenceWriteResult::RejectedVersion;
            }
            $parameters = $this->mapper->transition($propertyId, $transition, $version);
            if ($version === (int) $current['version']) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? PropertyLifecyclePersistenceWriteResult::AlreadyApplied
                    : PropertyLifecyclePersistenceWriteResult::StateConflict;
            }
            if ($version !== (int) $current['version'] + 1) {
                return PropertyLifecyclePersistenceWriteResult::RejectedVersion;
            }
            if ((string) $current['current_state'] !== $transition->from->value) {
                return PropertyLifecyclePersistenceWriteResult::StateConflict;
            }
            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return PropertyLifecyclePersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return PropertyLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function read(PropertyId $propertyId): PropertyLifecyclePersistenceReadResult
    {
        $row = $this->current($propertyId, false);
        if ($row === false) {
            return PropertyLifecyclePersistenceReadResult::missing($propertyId);
        }
        try {
            return PropertyLifecyclePersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return PropertyLifecyclePersistenceReadResult::corrupted($propertyId);
        }
    }

    /** @param callable(): PropertyLifecyclePersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): PropertyLifecyclePersistenceWriteResult
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

    private function lock(PropertyId $propertyId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:property_id,0))');
        $statement->execute(['property_id' => $propertyId->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(PropertyId $propertyId, bool $forUpdate): array|false
    {
        $sql = 'SELECT property_id::text,version,previous_state,current_state,action,transition_checksum FROM real_estate_catalog.property_lifecycle_transitions WHERE property_id=:property_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['property_id' => $propertyId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO real_estate_catalog.property_lifecycle_transitions(property_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:property_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }
}
