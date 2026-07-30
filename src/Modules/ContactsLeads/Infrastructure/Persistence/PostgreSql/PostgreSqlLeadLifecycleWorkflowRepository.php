<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceWriteResult;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlLeadLifecycleWorkflowRepository implements LeadLifecycleWorkflowStore
{
    public function __construct(private PDO $connection, private LeadLifecycleWorkflowMapper $mapper) {}

    public function initialize(LeadId $leadId, LeadLifecycleState $state): LeadLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($leadId, $state): LeadLifecyclePersistenceWriteResult {
            $this->lock($leadId);
            $current = $this->current($leadId, true);
            $parameters = $this->mapper->initial($leadId, $state);
            if ($current !== false) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? LeadLifecyclePersistenceWriteResult::AlreadyApplied
                    : LeadLifecyclePersistenceWriteResult::StateConflict;
            }
            $this->insert($parameters);

            return LeadLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function append(LeadId $leadId, LeadLifecycleTransition $transition, int $version): LeadLifecyclePersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($leadId, $transition, $version): LeadLifecyclePersistenceWriteResult {
            $this->lock($leadId);
            $current = $this->current($leadId, true);
            if ($current === false) {
                return LeadLifecyclePersistenceWriteResult::RejectedVersion;
            }
            $parameters = $this->mapper->transition($leadId, $transition, $version);
            if ($version === (int) $current['version']) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? LeadLifecyclePersistenceWriteResult::AlreadyApplied
                    : LeadLifecyclePersistenceWriteResult::StateConflict;
            }
            if ($version !== (int) $current['version'] + 1) {
                return LeadLifecyclePersistenceWriteResult::RejectedVersion;
            }
            if ((string) $current['current_state'] !== $transition->from->value) {
                return LeadLifecyclePersistenceWriteResult::StateConflict;
            }
            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return LeadLifecyclePersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return LeadLifecyclePersistenceWriteResult::Applied;
        });
    }

    public function read(LeadId $leadId): LeadLifecyclePersistenceReadResult
    {
        $row = $this->current($leadId, false);
        if ($row === false) {
            return LeadLifecyclePersistenceReadResult::missing($leadId);
        }
        try {
            return LeadLifecyclePersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return LeadLifecyclePersistenceReadResult::corrupted($leadId);
        }
    }

    /** @param callable(): LeadLifecyclePersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): LeadLifecyclePersistenceWriteResult
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

    private function lock(LeadId $leadId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:lead_id,0))');
        $statement->execute(['lead_id' => $leadId->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(LeadId $leadId, bool $forUpdate): array|false
    {
        $sql = 'SELECT lead_id::text,version,previous_state,current_state,action,transition_checksum FROM contacts_leads.lead_lifecycle_transitions WHERE lead_id=:lead_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['lead_id' => $leadId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO contacts_leads.lead_lifecycle_transitions(lead_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:lead_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }
}
