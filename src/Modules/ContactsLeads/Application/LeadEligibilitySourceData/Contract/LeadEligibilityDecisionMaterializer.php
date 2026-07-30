<?php

namespace Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterializationResult;

interface LeadEligibilityDecisionMaterializer
{
    public function materialize(LeadEligibilityMaterialization $materialization): LeadEligibilityMaterializationResult;
}
