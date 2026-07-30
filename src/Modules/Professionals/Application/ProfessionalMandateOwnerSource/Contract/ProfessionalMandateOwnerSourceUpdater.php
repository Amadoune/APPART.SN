<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceState;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceWriteResult;

interface ProfessionalMandateOwnerSourceUpdater
{
    public function replace(
        ProfessionalMandateOwnerSourceState $candidate,
        int $expectedVersion,
    ): ProfessionalMandateOwnerSourceWriteResult;
}
