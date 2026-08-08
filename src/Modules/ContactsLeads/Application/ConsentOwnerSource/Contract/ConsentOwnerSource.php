<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;

interface ConsentOwnerSource
{
    public function append(ConsentRevisionState $revision): ConsentRevisionWriteResult;

    public function at(LeadIngressIntentId $intentId, DateTimeImmutable $observedAt): ConsentRevisionReadResult;

    /** @return list<ConsentRevisionState> */
    public function history(LeadIngressIntentId $intentId): array;
}
