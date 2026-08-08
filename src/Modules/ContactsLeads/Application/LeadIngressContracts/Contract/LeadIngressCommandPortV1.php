<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Contract;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Command\SubmitLeadIngressV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressSubmissionResultV1;

interface LeadIngressCommandPortV1
{
    public function submit(SubmitLeadIngressV1 $command): LeadIngressSubmissionResultV1;
}
