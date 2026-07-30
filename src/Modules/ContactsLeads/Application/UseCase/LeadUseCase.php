<?php

namespace Appart\Modules\ContactsLeads\Application\UseCase;

use Appart\Modules\ContactsLeads\Application\Contract\LeadRegistry;
use Appart\Modules\ContactsLeads\Domain\Exception\LeadNotFound;
use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;

abstract readonly class LeadUseCase
{
    public function __construct(protected LeadRegistry $leads) {}

    /** @return array{Lead, int} */
    protected function load(LeadId $id): array
    {
        $lead = $this->leads->find($id) ?? throw new LeadNotFound;

        return [$lead, $lead->version()];
    }
}
