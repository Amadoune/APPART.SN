<?php

namespace App\Application\ProfessionalEndpoint\Contract;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;

interface ProfessionalEndpointRuntimeV1
{
    public function mandate(AccountId $accountId): ProfessionalMandateResolutionV1;

    public function status(AccountId $accountId): ProfessionalPublicStatusDecisionV1;
}
