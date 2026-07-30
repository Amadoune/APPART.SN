<?php

namespace Tests\Unit\Modules\ContactsLeads\Support;

use Appart\Modules\ContactsLeads\Application\Contract\LeadRegistry;
use Appart\Modules\ContactsLeads\Domain\Exception\ConcurrentLeadModification;
use Appart\Modules\ContactsLeads\Domain\Exception\LeadIdentityConflict;
use Appart\Modules\ContactsLeads\Domain\Exception\LogicalLeadDuplicate;
use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\Policy\LeadDeduplicationPolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadDeduplicationClaim;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;

final class FakeLeadRegistry implements LeadRegistry
{
    /** @var array<string, Lead> */
    private array $items = [];

    /** @var array<string, list<LeadDeduplicationClaim>> */
    private array $claims = [];

    private bool $fail = false;

    public function find(LeadId $id): ?Lead
    {
        return isset($this->items[$id->value]) ? clone $this->items[$id->value] : null;
    }

    public function add(Lead $lead, LeadDeduplicationClaim $claim): void
    {
        $this->guardFailure();
        if (isset($this->items[$lead->id()->value])) {
            throw new LeadIdentityConflict;
        }
        $policy = new LeadDeduplicationPolicy;
        foreach ($this->claims[$claim->signature->value] ?? [] as $existing) {
            if ($policy->conflicts($existing->occurredAt, $claim->occurredAt)) {
                throw new LogicalLeadDuplicate;
            }
        }
        $this->items[$lead->id()->value] = $this->clean($lead);
        $this->claims[$claim->signature->value][] = $claim;
    }

    public function save(Lead $lead, int $expectedVersion): void
    {
        $this->guardFailure();
        $stored = $this->items[$lead->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentLeadModification;
        }
        $this->items[$lead->id()->value] = $this->clean($lead);
    }

    public function failNextWrite(): void
    {
        $this->fail = true;
    }

    private function guardFailure(): void
    {
        if ($this->fail) {
            $this->fail = false;
            throw new ConcurrentLeadModification;
        }
    }

    private function clean(Lead $lead): Lead
    {
        $snapshot = clone $lead;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
