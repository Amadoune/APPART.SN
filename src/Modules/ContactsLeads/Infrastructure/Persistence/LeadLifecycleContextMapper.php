<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence;

use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;

final readonly class LeadLifecycleContextMapper
{
    /** @return array{lead_id:string,version:int,actor_id:string,occurred_at:string,context_checksum:string} */
    public function map(LeadLifecycleContextualAppend $append): array
    {
        $row = ['lead_id' => $append->leadId->value, 'version' => $append->nextVersion(), 'actor_id' => $append->context->actor->value, 'occurred_at' => $append->context->occurredAt->value->format('Y-m-d\TH:i:s.uP')];

        return $row + ['context_checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [(string) $row['lead_id'], (string) $row['version'], (string) $row['actor_id'], (string) $row['occurred_at']]));
    }
}
