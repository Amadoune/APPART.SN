<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

final readonly class AdministrativeActionDecisionContextResolution
{
    private function __construct(
        public AdministrativeActionDecisionContextResolutionStatus $status,
        public ?AdministrativeActionDecisionContext $context,
        public ?AdministrativeActionDecisionContextTechnicalDiagnostic $technicalDiagnostic,
    ) {}

    public static function available(AdministrativeActionDecisionContext $context): self
    {
        return new self(AdministrativeActionDecisionContextResolutionStatus::Available, $context, null);
    }

    public static function unavailable(AdministrativeActionDecisionContextTechnicalDiagnostic $diagnostic): self
    {
        return new self(AdministrativeActionDecisionContextResolutionStatus::Unavailable, null, $diagnostic);
    }
}
