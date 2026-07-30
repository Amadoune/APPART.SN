<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract;

use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;

interface ProfessionalMandateResolverV1
{
    /**
     * @param  non-empty-string  $accountId  Opaque canonical AccountId received from IAM.
     */
    public function resolve(string $accountId): ProfessionalMandateResolutionV1;
}
