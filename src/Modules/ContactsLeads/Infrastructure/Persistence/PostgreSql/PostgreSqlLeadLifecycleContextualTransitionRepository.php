<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlLeadLifecycleContextualTransitionRepository implements LeadLifecycleContextualTransitionStore
{
    public function __construct(private PDO $connection, private LeadLifecycleWorkflowStore $historical, private LeadLifecycleWorkflowMapper $workflowMapper, private LeadLifecycleContextMapper $contextMapper) {}

    public function read(LeadId $leadId): LeadLifecyclePersistenceReadResult
    {
        return $this->historical->read($leadId);
    }

    public function append(LeadLifecycleContextualAppend $append): LeadLifecycleContextualWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $this->lock($append->leadId);
            $current = $this->current($append->leadId);
            $transition = $this->workflowMapper->transition($append->leadId, $append->transition, $append->nextVersion());
            $context = $this->contextMapper->map($append);
            if ($current === false) {
                return $this->finish($owner, LeadLifecycleContextualWriteResult::VersionConflict);
            }
            if ((int) $current['version'] === $append->nextVersion()) {
                if (! hash_equals((string) $current['transition_checksum'], $transition['checksum'])) {
                    return $this->finish($owner, LeadLifecycleContextualWriteResult::StateConflict);
                }
                $stored = $this->context($append->leadId, $append->nextVersion());
                if ($stored === false) {
                    return $this->finish($owner, LeadLifecycleContextualWriteResult::Corrupted);
                }

                return $this->finish($owner, hash_equals((string) $stored['context_checksum'], $context['context_checksum']) ? LeadLifecycleContextualWriteResult::AlreadyApplied : LeadLifecycleContextualWriteResult::ContextDivergence);
            }
            if ((int) $current['version'] !== $append->expectedVersion) {
                return $this->finish($owner, LeadLifecycleContextualWriteResult::VersionConflict);
            }
            if ((string) $current['current_state'] !== $append->transition->from->value) {
                return $this->finish($owner, LeadLifecycleContextualWriteResult::StateConflict);
            }
            $this->insertTransition($transition);
            $this->insertContext($context);

            return $this->finish($owner, LeadLifecycleContextualWriteResult::Applied);
        } catch (Throwable $e) {
            if ($owner && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            } throw $e;
        }
    }

    private function finish(bool $owner, LeadLifecycleContextualWriteResult $result): LeadLifecycleContextualWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }

    private function lock(LeadId $id): void
    {
        $s = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:id,0))');
        $s->execute(['id' => $id->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(LeadId $id): array|false
    {
        $s = $this->connection->prepare('SELECT version,current_state,transition_checksum FROM contacts_leads.lead_lifecycle_transitions WHERE lead_id=:id ORDER BY version DESC LIMIT 1 FOR UPDATE');
        $s->execute(['id' => $id->value]);

        return $s->fetch(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|false */
    private function context(LeadId $id, int $version): array|false
    {
        $s = $this->connection->prepare('SELECT context_checksum FROM contacts_leads.lead_lifecycle_transition_contexts WHERE lead_id=:id AND version=:version');
        $s->execute(['id' => $id->value, 'version' => $version]);

        return $s->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $p */
    private function insertTransition(array $p): void
    {
        $s = $this->connection->prepare('INSERT INTO contacts_leads.lead_lifecycle_transitions(lead_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:lead_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $s->execute($p);
    }

    /** @param array<string, mixed> $p */
    private function insertContext(array $p): void
    {
        $s = $this->connection->prepare('INSERT INTO contacts_leads.lead_lifecycle_transition_contexts(lead_id,version,actor_id,occurred_at,context_checksum) VALUES(CAST(:lead_id AS uuid),:version,CAST(:actor_id AS uuid),CAST(:occurred_at AS timestamptz),:context_checksum)');
        $s->execute($p);
    }
}
