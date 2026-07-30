<?php

namespace Appart\Modules\ContactsLeads\Application\UseCase;

use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;

final readonly class CloseLead extends LeadUseCase
{
    public function execute(LeadId $id, LeadTimestamp $at): void
    {
        [$lead, $version] = $this->load($id);
        $lead->close($at);
        $this->leads->save($lead, $version);
    }
}
