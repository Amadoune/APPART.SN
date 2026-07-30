<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecycleStoredState;
use RuntimeException;
use Throwable;

final readonly class LeadLifecycleWorkflowMapper
{
    /** @return array{lead_id:string,version:int,previous_state:null,current_state:string,action:null,checksum:string} */
    public function initial(LeadId $leadId, LeadLifecycleState $state): array
    {
        $row = ['lead_id' => $leadId->value, 'version' => 1, 'previous_state' => null, 'current_state' => $state->value, 'action' => null];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array{lead_id:string,version:int,previous_state:string,current_state:string,action:string,checksum:string} */
    public function transition(LeadId $leadId, LeadLifecycleTransition $transition, int $version): array
    {
        $row = ['lead_id' => $leadId->value, 'version' => $version, 'previous_state' => $transition->from->value, 'current_state' => $transition->to->value, 'action' => $transition->action->value];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function snapshot(array $row): LeadLifecycleStoredState
    {
        try {
            $snapshot = new LeadLifecycleStoredState(
                LeadId::fromString((string) $row['lead_id']),
                LeadLifecycleState::from((string) $row['current_state']),
                (int) $row['version'],
            );
            if ($snapshot->version < 1 || ! hash_equals((string) $row['transition_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted lead lifecycle transition.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid lead lifecycle persistence row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            (string) $row['lead_id'],
            (string) $row['version'],
            $row['previous_state'] === null ? '' : (string) $row['previous_state'],
            (string) $row['current_state'],
            $row['action'] === null ? '' : (string) $row['action'],
        ]));
    }
}
