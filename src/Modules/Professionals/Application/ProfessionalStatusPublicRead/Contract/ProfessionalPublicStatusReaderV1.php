<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract;

use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;

interface ProfessionalPublicStatusReaderV1
{
    public function read(ProfessionalId $professionalId): ProfessionalPublicStatusDecisionV1;
}
