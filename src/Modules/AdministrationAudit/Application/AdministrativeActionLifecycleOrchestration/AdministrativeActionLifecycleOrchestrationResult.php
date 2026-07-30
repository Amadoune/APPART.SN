<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleDiagnostic;

final readonly class AdministrativeActionLifecycleOrchestrationResult
{
    public function __construct(
        public AdministrativeActionLifecycleOrchestrationStatus $status,
        public ?AdministrativeActionLifecycleDiagnostic $diagnostic = null,
    ) {}
}
