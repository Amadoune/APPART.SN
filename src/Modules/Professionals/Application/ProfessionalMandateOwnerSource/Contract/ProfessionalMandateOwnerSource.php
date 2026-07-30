<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceResult;

interface ProfessionalMandateOwnerSource
{
    /** @param non-empty-string $accountId */
    public function resolve(string $accountId): ProfessionalMandateOwnerSourceResult;
}
