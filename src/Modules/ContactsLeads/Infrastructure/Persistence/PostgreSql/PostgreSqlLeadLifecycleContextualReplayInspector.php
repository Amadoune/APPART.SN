<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextChecksum;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppendInspection;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final readonly class PostgreSqlLeadLifecycleContextualReplayInspector implements LeadLifecycleContextualReplayInspector
{
    public function __construct(private PDO $connection, private LeadLifecycleWorkflowMapper $workflowMapper, private LeadLifecycleContextMapper $contextMapper) {}

    public function inspectLatest(LeadId $leadId): LeadLifecycleContextualInspectionResult
    {
        $statement = $this->connection->prepare('SELECT t.lead_id::text,t.version,t.previous_state,t.current_state,t.action,t.transition_checksum,c.actor_id::text,c.occurred_at,c.context_checksum FROM contacts_leads.lead_lifecycle_transitions t JOIN contacts_leads.lead_lifecycle_transition_contexts c USING(lead_id,version) WHERE t.lead_id=:id ORDER BY t.version DESC LIMIT 1');
        $statement->execute(['id' => $leadId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return LeadLifecycleContextualInspectionResult::missing($leadId);
        }
        try {
            $occurredAt = (new DateTimeImmutable((string) $row['occurred_at']))->setTimezone(new DateTimeZone('UTC'));
            $canonicalContext = $row;
            $canonicalContext['occurred_at'] = $occurredAt->format('Y-m-d\TH:i:s.uP');
            if (! hash_equals((string) $row['transition_checksum'], $this->workflowMapper->checksum($row)) || ! hash_equals((string) $row['context_checksum'], $this->contextMapper->checksum($canonicalContext))) {
                return LeadLifecycleContextualInspectionResult::corrupted($leadId);
            }
            $snapshot = new LeadLifecycleContextualAppendInspection($leadId, (int) $row['version'], new LeadLifecycleTransition(LeadLifecycleState::from((string) $row['previous_state']), LeadLifecycleState::from((string) $row['current_state']), LeadLifecycleAction::from((string) $row['action'])), new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString((string) $row['actor_id']), LeadLifecycleOccurredAt::fromExplicitUtc($occurredAt)), LeadLifecycleContextChecksum::fromString((string) $row['context_checksum']));

            return LeadLifecycleContextualInspectionResult::found($snapshot);
        } catch (Throwable) {
            return LeadLifecycleContextualInspectionResult::corrupted($leadId);
        }
    }
}
