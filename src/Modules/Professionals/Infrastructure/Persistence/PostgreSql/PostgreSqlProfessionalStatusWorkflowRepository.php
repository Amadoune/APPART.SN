<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceWriteResult;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlProfessionalStatusWorkflowRepository implements ProfessionalStatusWorkflowStore
{
    public function __construct(private PDO $connection, private ProfessionalStatusWorkflowMapper $mapper) {}

    public function initialize(ProfessionalStatusId $professionalId): ProfessionalStatusPersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($professionalId): ProfessionalStatusPersistenceWriteResult {
            $this->lock($professionalId);
            $current = $this->current($professionalId, true);
            $parameters = $this->mapper->initial($professionalId);
            if ($current !== false) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum']) ? ProfessionalStatusPersistenceWriteResult::AlreadyApplied : ProfessionalStatusPersistenceWriteResult::StateConflict;
            }
            $this->insert($parameters);

            return ProfessionalStatusPersistenceWriteResult::Applied;
        });
    }

    public function append(ProfessionalStatusId $professionalId, ProfessionalStatusTransition $transition, int $version): ProfessionalStatusPersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($professionalId, $transition, $version): ProfessionalStatusPersistenceWriteResult {
            $this->lock($professionalId);
            $current = $this->current($professionalId, true);
            if ($current === false) {
                return ProfessionalStatusPersistenceWriteResult::RejectedVersion;
            }
            $parameters = $this->mapper->transition($professionalId, $transition, $version);
            if ($version === (int) $current['version']) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum']) ? ProfessionalStatusPersistenceWriteResult::AlreadyApplied : ProfessionalStatusPersistenceWriteResult::StateConflict;
            }
            if ($version !== (int) $current['version'] + 1) {
                return ProfessionalStatusPersistenceWriteResult::RejectedVersion;
            }
            if ((string) $current['current_state'] !== $transition->from->value) {
                return ProfessionalStatusPersistenceWriteResult::StateConflict;
            }
            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return ProfessionalStatusPersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return ProfessionalStatusPersistenceWriteResult::Applied;
        });
    }

    public function read(ProfessionalStatusId $professionalId): ProfessionalStatusPersistenceReadResult
    {
        $row = $this->current($professionalId, false);
        if ($row === false) {
            return ProfessionalStatusPersistenceReadResult::missing($professionalId);
        }
        try {
            return ProfessionalStatusPersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return ProfessionalStatusPersistenceReadResult::corrupted($professionalId);
        }
    }

    /** @param callable():ProfessionalStatusPersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): ProfessionalStatusPersistenceWriteResult
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

    private function lock(ProfessionalStatusId $professionalId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:professional_id,0))');
        $statement->execute(['professional_id' => $professionalId->value]);
    }

    /** @return array<string,mixed>|false */
    private function current(ProfessionalStatusId $professionalId, bool $forUpdate): array|false
    {
        $sql = 'SELECT professional_id::text,version,previous_state,current_state,action,transition_checksum FROM professionals.professional_status_transitions WHERE professional_id=:professional_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['professional_id' => $professionalId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string,mixed> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO professionals.professional_status_transitions(professional_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:professional_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }
}
