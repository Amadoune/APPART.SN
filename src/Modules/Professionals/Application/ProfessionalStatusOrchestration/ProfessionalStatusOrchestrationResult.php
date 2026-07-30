<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusDiagnostic;

final readonly class ProfessionalStatusOrchestrationResult
{
    public function __construct(
        public ProfessionalStatusOrchestrationStatus $status,
        public ?ProfessionalStatusDiagnostic $diagnostic = null,
    ) {}
}
