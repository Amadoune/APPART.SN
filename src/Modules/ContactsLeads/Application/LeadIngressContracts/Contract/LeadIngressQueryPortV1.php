<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Contract;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Query\ReadOwnLeadIngressReceiptV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressReadResultV1;

interface LeadIngressQueryPortV1
{
    public function readOwnReceipt(ReadOwnLeadIngressReceiptV1 $query): LeadIngressReadResultV1;
}
