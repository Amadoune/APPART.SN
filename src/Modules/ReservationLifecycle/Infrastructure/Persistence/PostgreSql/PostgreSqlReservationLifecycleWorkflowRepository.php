<?php

namespace Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceWriteResult;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlReservationLifecycleWorkflowRepository implements ReservationLifecycleWorkflowStore
{
    public function __construct(private PDO $connection, private ReservationLifecycleWorkflowMapper $mapper) {}

    public function initialize(ReservationId $reservationId, ReservationLifecycleState $state): ReservationLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($reservationId, $state): ReservationLifecyclePersistenceWriteResult {
            $this->lock($reservationId);
            $current = $this->current($reservationId, true);
            $parameters = $this->mapper->initial($reservationId, $state);
            if ($current !== false) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? ReservationLifecyclePersistenceWriteResult::AlreadyApplied
                    : ReservationLifecyclePersistenceWriteResult::StateConflict;
            }
            $this->insert($parameters);

            return ReservationLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function append(ReservationId $reservationId, ReservationLifecycleTransition $transition, int $version): ReservationLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($reservationId, $transition, $version): ReservationLifecyclePersistenceWriteResult {
            $this->lock($reservationId);
            $current = $this->current($reservationId, true);
            if ($current === false) {
                return ReservationLifecyclePersistenceWriteResult::RejectedVersion;
            }
            $parameters = $this->mapper->transition($reservationId, $transition, $version);
            if ($version === (int) $current['version']) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? ReservationLifecyclePersistenceWriteResult::AlreadyApplied
                    : ReservationLifecyclePersistenceWriteResult::StateConflict;
            }
            if ($version !== (int) $current['version'] + 1) {
                return ReservationLifecyclePersistenceWriteResult::RejectedVersion;
            }
            if ((string) $current['current_state'] !== $transition->from->value) {
                return ReservationLifecyclePersistenceWriteResult::StateConflict;
            }
            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return ReservationLifecyclePersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return ReservationLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function read(ReservationId $reservationId): ReservationLifecyclePersistenceReadResult
    {
        $row = $this->current($reservationId, false);
        if ($row === false) {
            return ReservationLifecyclePersistenceReadResult::missing($reservationId);
        }
        try {
            return ReservationLifecyclePersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return ReservationLifecyclePersistenceReadResult::corrupted($reservationId);
        }
    }

    /** @param callable(): ReservationLifecyclePersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): ReservationLifecyclePersistenceWriteResult
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

    private function lock(ReservationId $reservationId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:reservation_id,0))');
        $statement->execute(['reservation_id' => $reservationId->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(ReservationId $reservationId, bool $forUpdate): array|false
    {
        $sql = 'SELECT reservation_id::text,version,previous_state,current_state,action,transition_checksum FROM reservation_lifecycle.reservation_lifecycle_transitions WHERE reservation_id=:reservation_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['reservation_id' => $reservationId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO reservation_lifecycle.reservation_lifecycle_transitions(reservation_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:reservation_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }
}
