<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadResult;

interface ConsentOwnerSourceRuntimeReadPolicy
{
    public function reduce(ConsentRevisionReadResult $sourceResult): ConsentOwnerSourceRuntimeReadResult;
}
