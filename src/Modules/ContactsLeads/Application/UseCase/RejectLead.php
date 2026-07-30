<?php

namespace Appart\Modules\ContactsLeads\Application\UseCase;

use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadRejectionReason;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;

final readonly class RejectLead extends LeadUseCase
{
    public function execute(LeadId $id, LeadRejectionReason $reason, LeadTimestamp $at): void
    {
        [$lead, $version] = $this->load($id);
        $lead->reject($reason, $at);
        $this->leads->save($lead, $version);
    }
}
