<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

enum AdministrativeActionDecisionContextTechnicalDiagnostic: string
{
    case SourceUnavailable = 'source_unavailable';
    case CorruptedEvidence = 'corrupted_evidence';
}
