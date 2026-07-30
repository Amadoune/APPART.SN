<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract;

use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusTransitionRequest;

interface ProfessionalStatusOrchestrator
{
    public function execute(ProfessionalStatusTransitionRequest $request): ProfessionalStatusOrchestrationResult;
}
