<?php

namespace App\Application\ModerationResidualOperationalAuditContract;

enum ResidualOperationalAuditPathV1: string
{
    case DecisionApplied = 'moderation.decision.issued.v1';
    case CaseClosed = 'moderation.case.closed.v1';
    case ListingTargetCompleted = 'moderation.target-action.completed.v1';
}
